<?php

namespace App\Http\Requests\Ad;

use App\Enums\AdStatusEnum;
use App\Models\Ad;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AdStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can("create", Ad::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => "required|string",
            "description" => "required|string",
            "price" => "required|float",
            "user_id" => "required|integer|exists:users,id",
            "category_id" => "required|integer|exists:categories,id",
            "image" => "required|image|mime_type:jpg,jpeg,png"
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
