<?php

namespace Objectivehtml\Media\Http\Controllers;

use Illuminate\Http\Request;
use Objectivehtml\Media\Model;
use Illuminate\Support\Collection;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Http\Requests\StoreMediaRequest;
use Objectivehtml\Media\Http\Requests\UpdateMediaRequest;

class MediaController extends Collection
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json(Model::where(function($q) use ($request) {
            $q->orWhere('title', 'LIKE', '%'.($request->title ?: $request->q).'%');
            $q->orWhere('caption', 'LIKE', '%'.($request->caption ?: $request->q).'%');
            $q->orWhere('filename', 'LIKE', '%'.($request->filename ?: $request->q).'%');
            $q->orWhere('context', $request->context ?: $request->q);
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
        $file = $request->file(app(MediaService::class)->config('rest.key'));

        $model = app(MediaService::class)
            ->resource($file)
            ->model();

        $model->fill($request->only(['context', 'title', 'caption', 'meta']));
        $model->save();

        return response()->json($model);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(app(MediaService::class)->config('model')::findOrFail($id));
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
        $model = app(MediaService::class)->config('model')::findOrFail($id);
        $model->fill($request->all());
        $model->save();

        return response()->json($model);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = app(MediaService::class)->config('model')::findOrFail($id);
        $model->delete();

        return response()->json($model);
    }
}
