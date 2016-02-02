<?php

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$blogPosts = array(
    1 => array(
        'date'      => '2011-03-29',
        'author'    => 'igorw',
        'title'     => 'Using Silex',
        'body'      => '...',
    ),
);

$app = new Silex\Application();

$app->get('/', function () use ($app) {
    return $app->redirect('http://www.google.fr');
});

$app->get('/blog', function () use ($blogPosts) {
    $output = '';
    foreach ($blogPosts as $post) {
        $output .= $post['title'];
        $output .= '<br />';
    }

    return $output;
});

$app->get('/blog/{id}', function (Silex\Application $app, $id) use ($blogPosts) {
    if (!isset($blogPosts[$id])) {
        $app->abort(404, "Post $id does not exist.");
    }

    $post = $blogPosts[$id];

    return  "<h1>{$post['title']}</h1>".
            "<p>{$post['body']}</p>";
});

$app->post('/feedback', function (Request $request) {
    $message = $request->get('message');
    mail('feedback@yoursite.com', '[YourSite] Feedback', $message);

    return new Response('Thank you for your feedback!', 201);
});

$app->error(function (\Exception $e, $code) {
    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message);
});

$app->error(function (\LogicException $e, $code) {
    // this handler will only handle \LogicException exceptions
    // and exceptions that extends \LogicException
});

$app->view(function (array $controllerResult, Request $request) use ($app) {
    $acceptHeader = $request->headers->get('Accept');
    $bestFormat = $app['negotiator']->getBestFormat($acceptHeader, array('json', 'xml'));

    if ('json' === $bestFormat) {
        return new JsonResponse($controllerResult);
    }

    if ('xml' === $bestFormat) {
        return $app['serializer.xml']->renderResponse($controllerResult);
    }

    return $controllerResult;
});

$app->run();
