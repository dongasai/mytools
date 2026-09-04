<?php

namespace Modules\Demo5\DcatAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'sort_field' => ['sometimes', 'string', 'in:id,name,created_at,updated_at'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => '页码必须是整数',
            'page.min' => '页码最小为1',
            'per_page.integer' => '每页数量必须是整数',
            'per_page.min' => '每页数量最小为1',
            'per_page.max' => '每页数量最大为100',
            'search.string' => '搜索关键词必须是字符串',
            'search.max' => '搜索关键词最长255个字符',
            'sort_field.in' => '排序字段无效',
            'sort_direction.in' => '排序方向必须是asc或desc',
        ];
    }
}