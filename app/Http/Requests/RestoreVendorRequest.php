<?php

namespace App\Http\Requests;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RestoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('restore', Vendor::class);
    }

    public function rules(): array
    {
        return []; // No validation rules needed for restore
    }
}
