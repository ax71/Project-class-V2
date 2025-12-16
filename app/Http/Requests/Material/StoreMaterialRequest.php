<?php

namespace App\Http\Requests\Material;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:pdf,photo,word,video,ppt,xlsx',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,jpg,jpeg,png,webp|max:10240', // Max 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'Course ID is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'title.required' => 'Material title is required.',
            'content_type.required' => 'Content type is required.',
            'content_type.in' => 'Invalid content type selected.',
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'Invalid file type. Allowed types: PDF, Word, PowerPoint, Excel, Video (MP4), Images.',
            'file.max' => 'File size must not exceed 10MB.',
        ];
    }
}
