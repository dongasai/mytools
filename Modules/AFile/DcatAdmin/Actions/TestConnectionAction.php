<?php

namespace Modules\AFile\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\AFile\Services\StorageConfigService;

/**
 * 测试存储连接 Action
 *
 * 普通 AJAX 按钮，不覆盖 render2()
 */
class TestConnectionAction extends RowAction
{
    /**
     * 样式类（必须设置）
     *
     * @var array
     */
    protected $htmlClasses = ['action-primary'];

    /**
     * Action 标题
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
     * 处理 Action 请求
     *
     * @return Response
     */
    public function handle(): Response
    {
        $id = $this->getKey();

        // 使用静态方法调用
        $result = StorageConfigService::testConnectionById($id);

        if ($result['success']) {
            return $this->response()->success($result['message'])->refresh();
        }

        return $this->response()->error($result['message']);
    }
}