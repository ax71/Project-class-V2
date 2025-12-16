<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string|max:1000',
            'answers' => 'required|array|min:2|max:6', // Min 2, max 6 answer options
            'answers.*.answer_text' => 'required|string|max:500',
            'answers.*.is_correct' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'quiz_id.required' => 'Quiz ID is required.',
            'quiz_id.exists' => 'The selected quiz does not exist.',
            'question_text.required' => 'Question text is required.',
            'question_text.max' => 'Question text must not exceed 1000 characters.',
            'answers.required' => 'At least 2 answer options are required.',
            'answers.min' => 'Please provide at least 2 answer options.',
            'answers.max' => 'Maximum 6 answer options allowed.',
            'answers.*.answer_text.required' => 'Answer text is required for all options.',
            'answers.*.is_correct.required' => 'Please specify if the answer is correct.',
        ];
    }

    /**
     * Additional validation after basic rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $answers = $this->input('answers', []);
            $correctCount = collect($answers)->where('is_correct', true)->count();

            if ($correctCount === 0) {
                $validator->errors()->add('answers', 'At least one answer must be marked as correct.');
            }
        });
    }
}
