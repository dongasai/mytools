<?php

namespace Modules\Demo5\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueCaseInsensitive implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        private readonly string $table,
        private readonly string $column,
        private readonly ?int $ignoreId = null,
        private readonly ?string $ignoreColumn = 'id'
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('值必须是字符串');

            return;
        }

        $query = DB::table($this->table)
            ->whereRaw('LOWER('.$this->column.') = LOWER(?)', [trim($value)]);

        // 如果有忽略的ID，添加排除条件
        if ($this->ignoreId !== null) {
            $query->where($this->ignoreColumn, '!=', $this->ignoreId);
        }

        $exists = $query->exists();

        if ($exists) {
            $fail(str_replace(':attribute', $attribute, '该:attribute已存在'));
        }
    }
}
