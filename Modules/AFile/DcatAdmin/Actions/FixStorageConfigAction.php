<?php

namespace Modules\AFile\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\AFile\Models\FileStorageConfig;
use Modules\AFile\Services\FileStorageConfigCheckService;
use Modules\DcatAdmin\DcatAdmin\RowAction;

/**
 * 修复存储配置操作
 *
 * 当本地存储配置出现以下问题时显示：
 * - 路径不在项目目录内
 * - 路径不存在
 * - 缺少 root 配置
 * - 默认存储路径不正确（默认存储应为 storage/app）
 * - 缺少 url 配置
 * - 符号链接缺失或错误
 *
 * 使用 AJAX 方式自动修复配置
 */
class FixStorageConfigAction extends RowAction
{
    /**
     * 按钮样式类
     *
     * @var array
     */
    protected $htmlClasses = ['action-warning'];

    /**
     * Action 标题
     *
     * @return string
     */
    public function title()
    {
        return '<i class="fa fa-wrench"></i> 修复配置';
    }

    /**
     * 只在配置有问题时显示
     *
     * @return bool
     */
    public function allowed(): bool
    {
        $row = $this->getRow();

        if (!$row) {
            return false;
        }

        // 使用服务层检查配置
        $config = FileStorageConfig::find($row->id);
        if (!$config) {
            return false;
        }

        $issues = FileStorageConfigCheckService::check($config);

        return !empty($issues);
    }

    /**
     * 处理 Action 请求
     *
     * @return Response
     */
    public function handle(): Response
    {
        $id = $this->getKey();

        $config = FileStorageConfig::find($id);

        if (!$config) {
            return $this->response()->error('数据不存在或已被删除')->refresh();
        }

        // 使用服务层修复配置
        $result = FileStorageConfigCheckService::fix($config);

        if ($result['success']) {
            if (!empty($result['fixes'])) {
                $message = "配置已修复：\n" . implode("\n", $result['fixes']);
                return $this->response()->success($message)->refresh();
            }
            return $this->response()->info($result['message'])->refresh();
        }

        return $this->response()->error($result['message'])->refresh();
    }

    /**
     * 确认对话框
     *
     * @return array|string
     */
    public function confirm()
    {
        return [
            '确定要修复此存储配置吗？',
            '将自动修正路径配置、URL配置、创建必要目录和符号链接',
        ];
    }
}