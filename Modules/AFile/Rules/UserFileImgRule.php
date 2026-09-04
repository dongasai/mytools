<?php

namespace Modules\AFile\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\AFile\Models\FileImg;

/**
 * 用户图片文件验证规则
 *
 * 替代原 AFile\Validator\UserFileImgValidator 类
 * 验证图片文件是否属于指定用户
 */
class UserFileImgRule implements ValidationRule
{
    /**
     * 用户ID字段名
     */
    protected string $userField;

    /**
     * 文件信息
     */
    protected ?FileImg $file = null;

    /**
     * 验证结果
     */
    protected bool $isValid = false;

    /**
     * 错误消息
     */
    protected string $errorMessage;

    /**
     * 构造函数
     *
     * @param  string  $userField  用户ID字段名
     */
    public function __construct(string $userField = 'user_id')
    {
        $this->userField = $userField;
        $this->errorMessage = '文件不属于当前用户';
    }

    /**
     * 验证指定的属性
     *
     * @param  string  $attribute  属性名
     * @param  mixed  $value  属性值
     * @param  \Closure  $fail  失败回调
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        try {
            $userId = $this->getUserId();

            if (! $userId) {
                $fail('用户ID不能为空');

                return;
            }

            if (! $value) {
                $fail('文件ID不能为空');

                return;
            }

            // 验证文件是否属于用户
            $this->isValid = $this->check($userId, $value);

            if (! $this->isValid) {
                $fail($this->errorMessage);
            }

        } catch (\Exception $e) {
            $fail('文件验证失败: '.$e->getMessage());
        }
    }

    /**
     * 获取用户ID
     */
    protected function getUserId(): ?int
    {
        // 从请求数据中获取用户ID
        $request = request();
        $userId = $request->input($this->userField);

        if (! $userId) {
            // 尝试从当前登录用户获取
            $userId = auth()->id();
        }

        return $userId ? (int) $userId : null;
    }

    /**
     * 检查文件是否属于用户
     *
     * @param  int  $userId  用户ID
     * @param  mixed  $fileId  文件ID
     */
    public function check(int $userId, mixed $fileId): bool
    {
        try {
            $this->file = FileImg::query()
                ->where('user_id', $userId)
                ->where('id', $fileId)
                ->first();

            return $this->file !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取文件信息
     */
    public function getFile(): ?FileImg
    {
        return $this->file;
    }

    /**
     * 检查是否验证通过
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * 设置错误消息
     */
    public function setErrorMessage(string $message): static
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * 静态创建方法
     */
    public static function create(string $userField = 'user_id'): static
    {
        return new static($userField);
    }

    /**
     * 批量检查文件是否属于用户
     *
     * @param  int  $userId  用户ID
     * @param  array  $fileIds  文件ID数组
     */
    public static function checkMultiple(int $userId, array $fileIds): array
    {
        $result = [
            'valid' => [],
            'invalid' => [],
            'files' => [],
        ];

        if (empty($fileIds)) {
            return $result;
        }

        try {
            $files = FileImg::query()
                ->where('user_id', $userId)
                ->whereIn('id', $fileIds)
                ->get()
                ->keyBy('id');

            foreach ($fileIds as $fileId) {
                if ($files->has($fileId)) {
                    $result['valid'][] = $fileId;
                    $result['files'][$fileId] = $files->get($fileId);
                } else {
                    $result['invalid'][] = $fileId;
                }
            }

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 获取用户文件统计
     *
     * @param  int  $userId  用户ID
     */
    public static function getUserFileStats(int $userId): array
    {
        try {
            $stats = FileImg::query()
                ->where('user_id', $userId)
                ->selectRaw('
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size,
                    COUNT(CASE WHEN status = "active" THEN 1 END) as active_files,
                    COUNT(CASE WHEN status = "deleted" THEN 1 END) as deleted_files,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_files
                ')
                ->first();

            return [
                'total_files' => (int) ($stats->total_files ?? 0),
                'total_size' => (int) ($stats->total_size ?? 0),
                'active_files' => (int) ($stats->active_files ?? 0),
                'deleted_files' => (int) ($stats->deleted_files ?? 0),
                'recent_files' => (int) ($stats->recent_files ?? 0),
                'formatted_total_size' => self::formatFileSize($stats->total_size ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'total_files' => 0,
                'total_size' => 0,
                'active_files' => 0,
                'deleted_files' => 0,
                'recent_files' => 0,
            ];
        }
    }

    /**
     * 格式化文件大小
     */
    protected static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2).' '.$units[$unitIndex];
    }

    /**
     * 检查用户文件权限
     *
     * @param  int  $userId  用户ID
     * @param  int  $fileId  文件ID
     * @param  string  $permission  权限类型 read|write|delete
     */
    public static function checkPermission(int $userId, int $fileId, string $permission = 'read'): bool
    {
        $rule = new static;
        $isValid = $rule->check($userId, $fileId);

        if (! $isValid) {
            return false;
        }

        $file = $rule->getFile();
        if (! $file) {
            return false;
        }

        // 根据文件状态和权限类型检查权限
        switch ($permission) {
            case 'read':
                return in_array($file->status, ['active', 'processing']);
            case 'write':
                return $file->status === 'active';
            case 'delete':
                return $file->status === 'active';
            default:
                return false;
        }
    }

    /**
     * 获取用户最近文件
     *
     * @param  int  $userId  用户ID
     * @param  int  $limit  数量限制
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRecentFiles(int $userId, int $limit = 10)
    {
        try {
            return FileImg::query()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get(['id', 'file_name', 'file_size', 'mime_type', 'created_at']);
        } catch (\Exception $e) {
            return collect([]);
        }
    }
}
