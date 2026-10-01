<?php

namespace App\Http\Requests\Application;

class UpdateApplicationRequest extends StoreApplicationRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(fn (array $rules) => ['sometimes', ...$rules])
            ->all();
    }
}