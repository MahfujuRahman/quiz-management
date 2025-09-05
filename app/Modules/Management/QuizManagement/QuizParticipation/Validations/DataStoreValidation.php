<?php

namespace App\Modules\Management\QuizManagement\QuizParticipation\Validations;

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
            'quiz_id' => 'required | sometimes',
            'session_token' => 'required | sometimes',
            'name' => 'required | sometimes',
            'email' => 'required | sometimes',
            'phone' => 'required | sometimes',
            'organization' => 'required | sometimes',
            'answers' => 'required | sometimes',
            'obtained_marks' => 'required | sometimes',
            'percentage' => 'required | sometimes',
            'duration' => 'required | sometimes',
            'submit_reason' => 'required | sometimes',
            'is_completed' => 'required | sometimes',
            'is_passed' => 'required | sometimes',
            'started_at' => 'required | sometimes',
            'submitted_at' => 'required | sometimes',
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}