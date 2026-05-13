<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Override createApplication so that cached production config
     * (config:cache) is replaced with test-friendly settings before
     * RefreshDatabase and other traits attempt any DB connection.
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = parent::createApplication();

        $app['env'] = 'testing';
        $app['config']['app.env'] = 'testing';
        $app['config']['database.default'] = 'sqlite';
        $app['config']['database.connections.sqlite.database'] = ':memory:';
        $app['config']['session.driver'] = 'array';
        $app['config']['cache.default'] = 'array';
        $app['config']['mail.default'] = 'array';
        $app['config']['queue.default'] = 'sync';

        return $app;
    }
}
