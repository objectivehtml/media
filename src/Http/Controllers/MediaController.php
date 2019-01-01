<?php

namespace Objectivehtml\Media\Http\Controllers;

use Illuminate\Http\Request;
use Objectivehtml\Media\Model;
use Illuminate\Support\Collection;
use Objectivehtml\Media\MediaService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Objectivehtml\Media\Http\Requests\StoreMediaRequest;
use Objectivehtml\Media\Http\Requests\UpdateMediaRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MediaController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Define the controller middleware
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $middlewares = app(MediaService::class)->config('rest.middleware', []);

        foreach($middlewares as $key => $methods) {
            if(is_array($methods)) {
                $middleware = $this->middleware($key);

                foreach($methods as $method => $args) {
                    $middleware->$method(...$args);
                }
            }
            else {
                $this->middleware($methods);
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = app(MediaService::class)
            ->config('model', Model::class)::query()
            ->parents()
            ->with(app(MediaService::class)->config('rest.with', 'children'));

        return response()->json($query->where(function($q) use ($request) {
            if($value = $request->title ?: $request->q) {
                $q->orWhere('title', 'LIKE', '%'.$value.'%');
            }
            if($value = $request->caption ?: $request->q) {
                $q->orWhere('caption', 'LIKE', '%'.$value.'%');
            }
            if($value = $request->filename ?: $request->q) {
                $q->orWhere('filename', 'LIKE', '%'.$value.'%');
            }
            if($value = $request->context ?: $request->q) {
                $q->orWhere('context', $value);
            }
        })->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMediaRequest $request)
    {
        $file = $request->file(
            app(MediaService::class)->config('rest.input', 'file')
        );

        $model = app(MediaService::class)
            ->resource($file)
            ->model();

        $model->fill($request->only(['context', 'title', 'caption', 'meta']));
        $model->save();

        return response()->json($model->load(app(MediaService::class)->config('rest.with', 'children')));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(
            app(MediaService::class)->config('model', Model::class)::findOrFail($id)
                ->load(app(MediaService::class)->config('rest.with', 'children'))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMediaRequest $request, $id)
    {
        $model = app(MediaService::class)->config('model', Model::class)::findOrFail($id);
        $model->fill($request->all());
        $model->save();

        return response()->json(
            $model->load(app(MediaService::class)->config('rest.with', 'children'))
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = app(MediaService::class)->config('model', Model::class)::findOrFail($id);
        $model->load(app(MediaService::class)->config('rest.with', 'children'));
        $model->delete();

        return response()->json($model);
    }

    /**
     * Favorite the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function favorite($id)
    {
        $model = app(MediaService::class)->config('model', Model::class)::findOrFail($id);
        $model->favorite();

        return response()->json($model->load(app(MediaService::class)->config('rest.with', 'children')));
    }

    /**
     * Unfavorite the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unfavorite($id)
    {
        $model = app(MediaService::class)->config('model', Model::class)::findOrFail($id);
        $model->unfavorite();

        return response()->json($model->load(app(MediaService::class)->config('rest.with', 'children')));
    }

    /**
     * Re-encode a media resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function encode($id)
    {
        $model = app(MediaService::class)->config('model', Model::class)::findOrFail($id);

        if($model->ready) {
            return abort(400, 'Resource has already been encoded.');
        }

        $model->encode();

        return response()->json($model->load(app(MediaService::class)->config('rest.with', 'children')));
    }


}
