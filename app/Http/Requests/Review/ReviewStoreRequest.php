<?php

namespace App\Http\Requests\Review;

use App\Models\Review;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can("create", Review::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "user_id" => "required|int|exists:users,id",
            "comment" => "required|string",
            "rating" => "required|integer|min:1|max:5",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                "status" => false,
                "message" => "data validation failed",
                "errors" => $validator->errors()
            ], 422)
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                "status" => false,
                "message" => "sorry, can't perform this action",
            ], 401)
        );
    }
}
