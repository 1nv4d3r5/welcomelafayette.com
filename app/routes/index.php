<?php
use Welcomelafayette\Model\Thinkingmistake;
use Welcomelafayette\Model\Thoughtrecord;

$app->error(function (\Exception $e) use ($app) {
    $app->log->addError("Exception thrown: " . $e);
    $app->render('pages/error.html.twig', ['error_code'=>500]);
});

$app->get('/', function () use ($app) {
    $app->render('pages/index.html.twig', []);
});

$app->get('/search', function () use ($app) {
    $app->render('pages/search.html.twig', []);
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
