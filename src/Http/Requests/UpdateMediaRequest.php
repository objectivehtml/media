<?php

namespace Objectivehtml\Media\Http\Requests;

use Objectivehtml\Media\Model;
use Objectivehtml\Media\MediaService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Model $model)
    {
        return auth()->user() && auth()->user()->can('update', $model);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return app(MediaService::class)->config('rest.rules.update');
    }
}
