<?php

namespace App\Http\Requests;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RestoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('restore', Organization::class);
    }

    public function rules(): array
    {
        return []; // No validation rules needed for restore
    }
}
