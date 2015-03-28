<?php
use Welcomelafayette\Model\Thinkingmistake;
use Welcomelafayette\Model\Thoughtrecord;

$app->error(function (\Exception $e) use ($app) {
    $app->log->addError("Exception thrown: " . $e->getMessage());
    $app->render('pages/error.html', 500);
});

$app->get('/', function () use ($app) {
    $app->render('pages/index.html', array());
});

$app->get('/search', function () use ($app) {
    $app->render('pages/search.html', array());
});

$app->get('/submit', function () use ($app) {
    $app->render('pages/submit.html', array());
});

$app->post('/submit', function () use ($app) {
    $app->render('pages/submit.html', array());
});
