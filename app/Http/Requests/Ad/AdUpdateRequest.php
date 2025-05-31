<?php

namespace App\Http\Requests\Ad;

use App\Enums\AdStatusEnum;
use App\Models\Ad;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AdUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can("update", Ad::class);
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
            "category_id" => "required|integer|exists:categories,id",
            "image" => "nullable|image|mime_type:jpg,jpeg,png"
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
