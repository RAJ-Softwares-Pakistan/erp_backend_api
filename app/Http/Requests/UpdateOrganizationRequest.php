<?php

namespace App\Http\Requests;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->organization);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:256'],
            'address' => ['sometimes', 'string', 'max:512'],
            'phone' => ['sometimes', 'string', 'max:14'],
            'email' => ['sometimes', 'string', 'email', 'max:50', 'unique:organizations,email,' . $this->organization->organization_id . ',organization_id'],
            'logo' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:50', 'url'],
            'enable_gst' => ['sometimes', 'boolean'],
            'enable_witholding' => ['sometimes', 'boolean'],
            'ntn_no' => ['nullable', 'string', 'max:20'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'industry_type' => ['nullable', 'string', 'max:20'],
        ];
    }
}