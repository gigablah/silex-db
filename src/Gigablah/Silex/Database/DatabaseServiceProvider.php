<?php

namespace Gigablah\Silex\Database;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager;

/**
 * Integrates the Illuminate Database component from Laravel into Silex.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['illuminate.db.default_options'] = array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'database',
            'username'  => 'root',
            'password'  => 'password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        );

        $app['illuminate.db'] = $app->share(function ($app) {
            return $app['illuminate.capsule']->getConnection($app['illuminate.db.default_connection']);
        });

        $app['illuminate.db.options.initializer'] = $app->protect(function () use ($app) {
            if (!isset($app['illuminate.db.options'])) {
                $app['illuminate.db.options'] = array(
                    'default' => array()
                );
            }

            $tmp = $app['illuminate.db.options'];
            foreach ($tmp as $name => &$options) {
                $options = array_replace($app['illuminate.db.default_options'], $options);

                if (!isset($app['illuminate.db.default_connection'])) {
                    $app['illuminate.db.default_connection'] = $name;
                }
            }
            $app['illuminate.db.options'] = $tmp;
        });

        $app['illuminate.capsule'] = $app->share(function ($app) {
            $app['illuminate.db.options.initializer']();

            $capsule = new Manager();

            foreach ($app['illuminate.db.options'] as $name => $options) {
                $capsule->addConnection($options, $name);
            }

            return $capsule;
        });
    }

    public function boot(Application $app)
    {
    }
}
