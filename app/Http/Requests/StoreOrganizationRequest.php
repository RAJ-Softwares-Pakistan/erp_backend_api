<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create an organization
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:256'],
            'address' => ['required', 'string', 'max:512'],
            'phone' => ['required', 'string', 'max:14'],
            'email' => ['required', 'string', 'email', 'max:50', 'unique:organizations'],
            'logo' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:50', 'url'],
            'enable_gst' => ['boolean'],
            'enable_witholding' => ['boolean'],
            'ntn_no' => ['nullable', 'string', 'max:20'],
            'currency' => ['required', 'string', 'size:3'],
            'industry_type' => ['nullable', 'string', 'max:20'],
        ];
    }
}