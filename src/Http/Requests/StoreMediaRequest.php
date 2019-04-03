<?php

namespace Objectivehtml\Media\Http\Requests;

use Objectivehtml\Media\Services\MediaService;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user() && auth()->user()->can('create', app(MediaService::class)->config('model'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return app(MediaService::class)->config('rest.rules.store');
    }
}
