<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\File;

class UploadImageRequest extends FormRequest
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
     *
     * @return array<string, array<int, string|File|Dimensions>>
     */
    public function rules(): array
    {
        return [
            'upload' => [
                'required',
                File::image()->max(2048),
                Rule::dimensions()->maxWidth(1200)->maxHeight(1200),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'upload.required'   => 'Please select an image to upload.',
            'upload.image'      => 'The uploaded file must be an image (JPEG, PNG, BMP, GIF, SVG, or WebP).',
            'upload.max'        => 'The image size cannot exceed 2MB.',
            'upload.dimensions' => 'The image dimensions must not exceed 1200x1200 pixels.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json(
            data: [
                'error' => ['message' => $validator->errors()->first()],
            ],
            status: 413
        ));
    }
}
