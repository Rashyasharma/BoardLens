<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadComponentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'series_id' => 'required|exists:exam_series,id',
            'subject_id' => 'required|exists:subjects,id',
            'components_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ];
    }
}
