<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'     => 'digits_between:5,5',
            'new_price' => 'required|integer',
            'image'     => 'required',
            'start_at'  => 'required|date',
            'end_at'    => 'required|date',
        ];
    }

    /**
     * 取得已定義驗證規則的錯誤訊息。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.digits_between' => '優惠券代號 請填寫五碼數字',
            'new_price.required'   => '售價 請填寫',
            'new_price.integer'    => '售價 請填寫數字',
            'image.required'       => '圖片 請透過本站上傳',
            'start_at.required'    => '開始時間 請確認',
            'start_at.date'        => '開始時間 請確認',
            'end_at.required'      => '結束時間 請確認',
            'end_at.date'          => '結束時間 請確認',
        ];
    }
}
