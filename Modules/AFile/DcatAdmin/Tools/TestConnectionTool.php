<?php

namespace Modules\AFile\DcatAdmin\Tools;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Show\AbstractTool;
use Modules\AFile\Services\StorageConfigService;

/**
 * 详情页测试存储连接 Tool
 *
 * 用于 Show 页面的 tools，继承 Show\AbstractTool
 */
class TestConnectionTool extends AbstractTool
{
    /**
     * Tool 标题
     *
     * @return string
     */
    public function title()
    {
        return '<i class="fa fa-plug"></i> 测试连接';
    }

    /**
     * 确认对话框
     *
     * @return array|string
     */
    public function confirm()
    {
        return ['确定要测试此存储连接吗？', '将尝试创建并删除测试文件'];
    }

    /**
     * 处理 Tool 请求
     *
     * @return Response
     */
    public function handle(): Response
    {
        $id = $this->getKey();

        if (!$id) {
            return $this->response()->error('缺少存储配置ID')->refresh();
        }

        // 使用静态方法调用
        $result = StorageConfigService::testConnectionById($id);

        if ($result['success']) {
            return $this->response()->success($result['message'])->refresh();
        }

        return $this->response()->error($result['message']);
    }
}