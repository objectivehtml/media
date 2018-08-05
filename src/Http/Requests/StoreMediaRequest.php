<?php

namespace Objectivehtml\Media\Http\Requests;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user() && auth()->user()->can('create', Model::class);
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
