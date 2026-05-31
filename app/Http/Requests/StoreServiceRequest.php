<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\Business;
use Illuminate\Http\Request;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'business_id' => ['required','exists:businesses,id'],
            'name' => ['required','string','max:150'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0'],
            'base_duration' => ['required','integer','min:1'] // بالدقائق
        ];
    }
}