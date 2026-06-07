<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware handles auth/role
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'qualification_id' => 'required|exists:qualifications,id',
            'series_id' => 'required|exists:exam_series,id',
            'subject_id' => 'required|exists:subjects,id',
            'results_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ];
    }
}
