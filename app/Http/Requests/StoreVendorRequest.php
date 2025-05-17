<?php

namespace App\Http\Requests;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Vendor::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:vendors'],
            'organization_id' => ['required', 'exists:organizations,organization_id']
        ];
    }
}