<?php

namespace Tests;

use Queue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Objectivehtml\Media\AppServiceProvider;
use Objectivehtml\Media\Facades\Media as Facade;
use Intervention\Image\ImageManagerStatic as Image;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function user()
    {
        $model = app(\Illuminate\Foundation\Auth\User::class);
        $model->name = 'test';
        $model->email = 'test@test.com';
        $model->password = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm'; // secret
        $model->remember_token = Str::random(10);
        $model->save();

        return $model;
    }

    /**
    * Setup the test environment.
    */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->artisan('migrate', [
            '--database' => 'testbench'
        ]);

        Storage::fake('s3');
        Storage::fake('local');
        Storage::fake('public');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            AppServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Media' => Facade::class
        ];
    }

}
