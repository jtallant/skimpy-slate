<?php namespace Skimpy\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class Skimpy implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['skimpy'] = new \Skimpy\Skimpy($app['contentFileFinder']);
    }

    public function boot(Application $app)
    {

    }
}