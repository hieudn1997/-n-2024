<?php

namespace App\Http\Requests;

use App\Rules\FundAllocation;
use Illuminate\Foundation\Http\FormRequest;

class CreatePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:45',
            'allocation' => ['required', 'array', 'min:1', new FundAllocation],
            'allocation.*.fund_id' => 'required|numeric|exists:funds,id|distinct:strict',
            'allocation.*.percentage' => 'required|integer|min:1|max:100',
        ];
    }
}
