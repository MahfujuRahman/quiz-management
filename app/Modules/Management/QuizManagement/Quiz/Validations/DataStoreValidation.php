<?php

namespace App\Modules\Management\QuizManagement\Quiz\Validations;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DataStoreValidation extends FormRequest
{
    /**
     * Determine if the  is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * validateError to make this request.
     */
    public function validateError($data)
    {
        $errorPayload =  $data->getMessages();
        return response(['status' => 'validation_error', 'errors' => $errorPayload], 422);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->validateError($validator->errors()));
        if ($this->wantsJson() || $this->ajax()) {
            throw new HttpResponseException($this->validateError($validator->errors()));
        }
        parent::failedValidation($validator);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required | sometimes',
            'description' => 'required | sometimes',
            'total_question' => 'required | sometimes',
            'exam_start_datetime' => ['required', 'date', 'after_or_equal:' . now()->toDateTimeString()],
            'exam_end_datetime'   => ['required', 'date', 'after_or_equal:exam_start_datetime'],
            'total_mark' => 'required | sometimes',
            'pass_mark' => 'required | sometimes',
            'is_negative_marking' => 'required | sometimes',
            'negative_value' => ' sometimes',
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'slug' => 'required | sometimes | unique:quizzes,slug,' . ($this->id ?? 'NULL') . ',id,deleted_at,NULL',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.required' => 'The Quiz Code field is required.',
            'slug.unique' => 'The Quiz Code has already been taken. You can choose another one.',
        ];
        }
    }