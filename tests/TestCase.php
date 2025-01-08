<?php

namespace Jasotacademy\FileVersionControl\Tests;

use Jasotacademy\FileVersionControl\FileVersionControlServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
          FileVersionControlServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('file_version_control.storage_disk', 'testing');
        include_once __DIR__.'/../database/migrations/create_file_version_control_table.php.stub';
        (new \CreateFileVersionsTable())->up();
    }
}