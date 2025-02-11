<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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

    protected function getValidatorInstance()
    {
        $old_year = $this->input('old_year');
        $old_month = $this->input('old_month');
        $old_day = $this->input('old_day');
        $birth_day = $old_year . '-' . $old_month . '-' . $old_day;

        $this->merge([
            'birth_day' => $birth_day,
        ]);
        return parent::getValidatorInstance();
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
                'over_name' => 'required|string|max:10',
                'under_name' => 'required|string|max:10',
                'over_name_kana' => 'required|string|regex:/^[ァ-ヶー]+$/u|max:30',
                'under_name_kana' => 'required|string|regex:/^[ァ-ヶー]+$/u|max:30',
                'mail_address' => 'required|email:filter|unique:users|max:100',
                'sex' => 'required|in:1,2,3',
                'birth_day' => 'required|before:yesterday|after:1999-12-31',
                'role' => 'required|in:1,2,3,4',
                'password' => 'required|min:8|max:30|confirmed',
                'password_confirmation' => 'required|min:8|max:30',
        ];
    }

    public function messages(){
    return [
        'over_name.required' => '※性は必須です',
        'under_name.required' => '※名は必須です',
        'over_name.max' => '※10文字まで入力してください',
        'under_name.max' => '※10文字まで入力してください',
        'over_name_kana.required' => '※カタカナは必須です',
        'under_name_kana.required' => '※カタカナは必須です',
        'over_name_kana.regex' => '※カタカナで入力してください',
        'under_name_kana.regex' => '※カタカナで入力してください',
        'mail_address.required'  => '※メールアドレスは必須です',
        'mail_address.email'  => '※メール形式で入力してください',
        'mail_address.unique'  => '※すでに登録済みのメールアドレスです',
        'mail_address.max'  => '※100文字まで入力してください',
        'birth_day.required' => '※生年月日が未入力です',
        'birth_day.after' => '※2000年1月1日から今日まで入力してください',
        'birth_day.before' => '※2000年1月1日から今日まで入力してください',
        'password.confirmed' => '※パスワードと確認用と一致させてください',
        'password.min' => '※パスワードは8文字以上30文字以内で入力してください',
        'password.max' => '※パスワードは8文字以上30文字以内で入力してください',
        'password.required' => '※パスワードは必須です',
        ];
    }
}
