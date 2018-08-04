<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
use Objectivehtml\Media\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Events\VideoEncodingProgress;

class CopyAndEncodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    protected $encoded;

    protected $audioKbps = 256;

    protected $audioChannels = 2;

    protected $extension = 'mp4';

    protected $mime = 'image/mp4';

    protected $height = null;

    protected $threads = 24;

    protected $timeout = 0;

    protected $videoKbps = 1000;

    protected $width = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, array $options = [])
    {
        $this->model = $model;

        foreach($options as $key => $value) {
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = $this->model();

        if(!$model->meta->get('encoding')) {
            $model->meta('encoding', true);
            $model->save();

            $path = $this->model->path;

            if($original = $this->model->children()->original()->first()) {
                $path = $this->model->children()->original()->first()->path;
            }

            $video = app(MediaService::class)->ffmpeg()->open($path);

            if($this->width && $this->height) {
                $video->filters()
                    ->resize(new Dimension($this->width, $this->height))
                    ->synchronize();
            }

            $format = (new X264('aac'))
                ->setAdditionalParameters(['-strict', '-2'])
                ->setKiloBitrate($this->videoKbps)
                ->setAudioChannels($this->audioChannels)
                ->setAudioKiloBitrate($this->audioKbps)
                ->on('progress', function($video, $format, $percentage) use ($model) {
                    $model->meta('encoded_percent', $percentage);
                    $model->save();

                    event(new VideoEncodingProgress($model));
                });

            $video->save($format, $output = $model->path);

            $model->meta('encoding', null);
            $model->meta('encoded', true);
            $model->size = app(MediaService::class)->storage()->disk($model->disk)->size($model->relative_path);

            $model->save();
        }
    }

    protected function model(): Model
    {
        $model = app(MediaService::class)->model([
            'size' => 0,
            'mime' => $this->mime,
            'context' => 'encoded',
            'extension' => $this->extension,
            'directory' => $this->model->directory,
            'orig_filename' => $this->model->orig_filename
        ]);

        $model->parent()->associate($this->model);
        $model->save();

        return $model;
    }

}
