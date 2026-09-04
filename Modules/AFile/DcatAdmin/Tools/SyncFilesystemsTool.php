<?php

namespace Modules\AFile\DcatAdmin\Tools;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;
use Modules\AFile\Services\StorageConfigService;

/**
 * 同步文件系统配置工具
 */
class SyncFilesystemsTool extends AbstractTool
{
    /**
     * 按钮样式
     *
     * @var string
     */
    protected $style = 'btn btn-primary';

    /**
     * 按钮文本
     *
     * @return string
     */
    public function title()
    {
        return '同步public储存到数据库';
    }

    /**
     * 确认弹窗
     *
     * @return array|string
     */
    public function confirm()
    {
        return [
            '确定要同步 filesystems.php 配置到数据库吗？',
            '这将读取 config/filesystems.php 中的磁盘配置并同步到数据库'
        ];
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @return \Dcat\Admin\Actions\Response
     */
    public function handle(Request $request)
    {
        try {
            $result = StorageConfigService::syncFromFilesystems(
                app()->environment(),
                0
            );

            $message = sprintf(
                '同步成功！新增：%d，更新：%d，跳过：%d',
                $result['created'],
                $result['updated'],
                $result['skipped']
            );

            return $this->response()->success($message)->refresh();
        } catch (\Exception $e) {
            return $this->response()->error('同步失败：' . $e->getMessage());
        }
    }
}