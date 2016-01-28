<?php
/**
 * Routes
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Home page
 */
$app->get('/', function() use ($app) {

    # TODO: Remove this after CLI command is done
    $app['db_populator']->populate();

    $posts = $app['skimpy']->findBy(['type' => 'post'], ['date' => 'DESC']);

    $data = [
        'seotitle' => 'Home',
        'posts' => $posts
    ];

    return $app->render('home.twig', $data);
})
->bind('home');

/**
 * Render category or tag archive
 *
 * Examples URIs:
 * /category/{term-slug}
 * /tag/{term-slug}
 */
$app->get('/{taxonomySlug}/{termSlug}', function($taxonomySlug, $termSlug) use ($app) {

    $archive = $app['skimpy']->getArchive($taxonomySlug, $termSlug);

    if (is_null($archive)) {
        $app->abort(404);
    }

    return $app->render(
        'archive.twig',
        [
            'archiveName' => 'Archive Name',
            'seotitle'    => 'Archive Name',
            'items'       => $archive
        ]
    );
})
->bind('archive');

/**
 * Render a page or post
 */
$app->get('/{slug}', function($slug) use ($app) {

    $contentItem = $app['skimpy']->findOneBySlug($slug);

    if (is_null($contentItem)) {
        $app->abort(404);
    }

    return $app->render(
        $contentItem->getTemplate().'.twig',
        ['contentItem' => $contentItem]
    );
})
->bind('content');

/**
 * Handle 404 errors
 */
$app->error(function(HttpException $e, $code) use ($app) {

    if (404 !== $code) {
        return;
    }

    return new Response(
        $app['twig']->render(
            '404.twig',
            ['seotitle' => '404 Not Found']
        ),
        404
    );
});