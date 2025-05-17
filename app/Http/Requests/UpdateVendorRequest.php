<?php

namespace App\Http\Requests;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->vendor);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'contact_person' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:vendors,email,' . $this->vendor->id],
            'organization_id' => ['sometimes', 'exists:organizations,organization_id']
        ];
    }
}