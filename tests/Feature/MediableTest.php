<?php

namespace Tests\Feature;

use Media;
use Tests\User;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Objectivehtml\Media\Filters\Image\Greyscale;

class MediableTest extends TestCase
{

    public function testAddingFromFile()
    {
        $file = UploadedFile::fake()->image('test.jpeg', 1, 1);

        $model = ($user = $this->user())->addMedia($file, function($resource) {
            $resource->filter(new Greyscale);
            $resource->tag('avatar');
            $resource->meta([
                'some_custom_attribute' => 'some value'
            ]);
        });

        $this->assertCount(1, $model->filters);
        $this->assertCount(1, $model->children()->context('thumbnail')->get());
        $this->assertCount(1, $user->media);
    }

    public function testAddMultipleFilesFromRequest()
    {
        $request = new Request([], [], [], [], [
            'files' => [
                UploadedFile::fake()->image('1.jpeg', 1, 1),
                UploadedFile::fake()->image('2.jpeg', 1, 1),
                UploadedFile::fake()->image('3.jpeg', 1, 1),
            ]
        ]);

        $files = ($user = $this->user())->addMediaFromRequest($request, function($resource) {
            $resource->preserveOriginal(false);
        });

        $this->assertCount(count($request->file('files')), $files);

        $user->media()->detach($files->first());
        $user->media()->detach($files->last());

        $this->assertCount(1, $user->media);

    }

    public function testAddFilesFromFileBag()
    {
        $request = new Request([], [], [], [], [
            'files' => [
                UploadedFile::fake()->image('1.jpeg', 1, 1),
                UploadedFile::fake()->image('2.jpeg', 1, 1),
                UploadedFile::fake()->image('3.jpeg', 1, 1),
            ]
        ]);

        $files = ($user = $this->user())->addMediaFromRequest($request->files, function($resource) {
            $resource->preserveOriginal(false);
        });

        $this->assertCount(count($request->file('files')), $files);

    }

    public function user(array $attributes = [])
    {
        $faker = Factory::create();

        return User::create(array_merge([
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => $faker->password
        ], $attributes));
    }
}
