<?php

namespace Jasotacademy\FileVersionControl\Tests;

use CreateFileVersionsTable;
use CreateRollbackLogsTable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Factories\Factory;
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
        $app['router']->group(['namespace' => 'Jasotacademy\FileVersionControl\Http\Controllers'], function () {
            include __DIR__ . '/../routes/api.php';
        });

        include_once __DIR__.'/../database/migrations/create_file_versions_table.php.stub';
        include_once __DIR__.'/../database/migrations/create_rollback_logs_table.php.stub';
        (new CreateFileVersionsTable())->up();
        (new CreateRollbackLogsTable())->up();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws BindingResolutionException
     */
    protected function loadFactories(): void
    {
        $this->app->make(Factory::class)
            ->load(__DIR__ . '/../src/Database/Factories');
    }
}