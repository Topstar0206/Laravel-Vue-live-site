<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use App\Library\Api;

class PaymentRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
      return [
        'type' => 'required|in:1,2',
        'payment_type' => 'required|in:1,2',
        'id' => 'required'
      ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $data['data']['error'] = $validator->errors();
        $statusCode = 422;
        $rst = Api::ApiResponse($data,$statusCode);
        throw new \Illuminate\Validation\ValidationException($validator, $rst);
    }
}
