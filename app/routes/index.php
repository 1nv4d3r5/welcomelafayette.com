<?php
use Welcomelafayette\Model\Thinkingmistake;
use Welcomelafayette\Model\Thoughtrecord;

$app->error(function (\Exception $e) use ($app) {
    $app->log->addError("Exception thrown: " . $e);
    $app->render('pages/error.html.twig', ['error_code'=>500]);
});

$app->notFound(function () use ($app) {
    $app->render('pages/error.html.twig', ['error_code'=>404, 'error_msg'=>'Not found']);
});

$app->get('/', function () use ($app) {
    $org = new \Welcomelafayette\Model\Organization($app->getConfig());
    $orgs = $org->getAll(4, 0, ['approved'=>1], ['date_created'=>'DESC']);
    $app->render('pages/index.html.twig', ['orgs' => $orgs]);
});

$app->get('/search', function () use ($app) {
    $org = new \Welcomelafayette\Model\Organization($app->getConfig());
    $orgs = $org->getAll(50, 0, ['approved'=>1], ['name'=>'ASC']);
    $app->render('pages/search.html.twig', ['orgs' => $orgs]);
});

$app->get('/submit', function () use ($app) {
    $app->render('pages/submit.html.twig', []);
});

$app->post('/submit', function () use ($app) {
    $form_errors = null;
    $v = new Valitron\Validator($_POST);
    $v->rule('required', [
        'name',
        'address1',
        'city',
        'state',
        'zip',
        'phone',
        'email',
        'description'
    ]);
    $v->rule('email', 'email');
    $v->rule('url', ['facebook_url', 'website_url']);
    if (!$v->validate()) {
        $form_errors = $v->errors();
    }

    /**
     * handle file upload
     */
    $img_url = null;
    $img_errors = null;
    if (!empty($_FILES['img']) && !empty($_FILES['img']['name'])) {
        $storage = new \Upload\Storage\FileSystem($app->config('org.image.uploads'));
        $file = new \Upload\File('img', $storage);
        $file->setName(md5(uniqid('', true)));
        $file->addValidations([
            new \Upload\Validation\Mimetype([
                'image/png',
                'image/gif',
                'image/jpeg',
                'image/jpg'
            ]),
            new \Upload\Validation\Size('5M'),
        ]);
        try {
            // Success!
            $file->upload();
            $img_url = "/org_images/" . $file->getNameWithExtension();
            $_POST['img_url'] = $img_url;
        } catch (\Exception $e) {
            // Fail!
            $img_errors = $file->getErrors();
        }
    }

    if ($img_errors || $form_errors) {
        $app->render('pages/submit.html.twig', [
            'img_errors' => $img_errors,
            'form_errors' => $form_errors
        ]);
    } else {
        $strip_tags_filter = [
            'filter' => FILTER_SANITIZE_STRING,
            'flags' => FILTER_FLAG_NO_ENCODE_QUOTES
        ];

        $org = new \Welcomelafayette\Model\Organization($app->getConfig());
        $input = filter_input_array(
            INPUT_POST,
            [
                'name' => $strip_tags_filter,
                'address1' => $strip_tags_filter,
                'address2' => $strip_tags_filter,
                'city' => $strip_tags_filter,
                'state' => $strip_tags_filter,
                'zip' => $strip_tags_filter,
                'phone' => $strip_tags_filter,
                'email' => FILTER_SANITIZE_EMAIL,
                'description' => $strip_tags_filter,
                'twitter' => $strip_tags_filter,
                'facebook_url' => FILTER_SANITIZE_URL,
                'website_url' => FILTER_SANITIZE_URL,
            ],
            true
        );
        $input = $input + ['img_url' => $img_url, 'approved' => 0];

        $new_id = $org->save($input + ['img_url' => $img_url]);
        $app->flash('info', 'Submission received! It will be reviewed for approval. Thanks!');
        $app->redirect('/submit');
    }
});

$app->get('/show/:id', function ($id) use ($app) {
    $org = new \Welcomelafayette\Model\Organization($app->getConfig());
    $row = $org->getApprovedById((int)$id);
    if (!$row) {
        $app->notFound();
    }
    $app->render('pages/show.html.twig', $row);
});


$app->get('/org/images/', function () use ($app) {
    // Setup Glide server
    $server = League\Glide\ServerFactory::create([
        'source' => $app->config('org.image.uploads'),
        'cache' => $app->config('org.image.uploads') . "/modified/",
        'max_image_size' => 2000*2000,
    ]);

    $server->outputImage($_GET['p'], ['w' => $_GET['w'], 'h' => $_GET['h']]);
});
