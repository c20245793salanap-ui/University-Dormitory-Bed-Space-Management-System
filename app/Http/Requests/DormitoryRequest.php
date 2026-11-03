<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

abstract class DormitoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = array_map(
            static fn (mixed $value): mixed => is_string($value) ? trim(strip_tags($value)) : $value,
            $this->all(),
        );

        if (isset($normalized['student_id']) && is_string($normalized['student_id'])) {
            $normalized['student_id'] = Str::upper(preg_replace('/\s+/', '', $normalized['student_id']));
        }

        if (isset($normalized['building_name']) && is_string($normalized['building_name'])) {
            $normalized['building_name'] = Str::title($normalized['building_name']);
        }

        $this->merge($normalized);
    }
}
