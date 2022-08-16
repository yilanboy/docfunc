<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        // Using policy for Authorization
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'min:4', 'max:50'],
            'category_id' => ['required', 'numeric', 'exists:categories,id'],
            'preview_url' => ['nullable', 'url', 'regex:/(.jpeg|.JPEG|.jpg|.JPG|.png|.PNG)$/u'],
            'body' => ['required'],
            'remove_tags_and_newline_body' => ['min:500', 'max:20000'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => '請填寫標題',
            'title.min' => '標題至少 4 個字元',
            'title.max' => '標題至多 50 個字元',
            'category_id.required' => '請選擇文章分類',
            'category_id.numeric' => '分類資料錯誤',
            'category_id.exists' => '分類不存在',
            'preview_url.url' => '預覽圖連結有誤',
            'preview_url.regex' => '預覽圖連結非圖片格式 ( jpeg | jpg | png )',
            'body.required' => '請填寫文章內容',
            'remove_tags_and_newline_body.min' => '文章內容至少 500 個字元',
            'remove_tags_and_newline_body.max' => '文章內容字數已超過限制',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'remove_tags_and_newline_body' => preg_replace('/[\r\n]/u', '', strip_tags($this->body)),
        ]);
    }
}
