<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'marks_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'series_id' => 'required|exists:exam_series,id',
            'subject_id' => 'required|exists:subjects,id',
        ];
    }
}
