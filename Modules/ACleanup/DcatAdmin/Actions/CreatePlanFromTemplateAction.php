<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Services\ACleanService;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

/**
 * 从模板创建计划Action
 *
 * 用于从模板创建新的清理计划
 */
class CreatePlanFromTemplateAction extends AbstractTool
{
    /**
     * 按钮标题
     */
    protected $title = '从模板创建';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $templateId = $request->input('template_id');
        $planName = $request->input('plan_name');

        if (empty($templateId)) {
            return $this->response()
                ->error('请选择模板');
        }

        if (empty($planName)) {
            return $this->response()
                ->error('请输入计划名称');
        }

        // 调用服务从模板创建计划
        $result = ACleanService::createPlanFromTemplate($templateId, $planName);

        if (! $result['success']) {
            return $this->response()
                ->error('创建失败：'.$result['message']);
        }

        $plan = $result['data'];

        return $this->response()
            ->success('计划创建成功！')
            ->detail("
                计划ID：{$plan['id']}<br>
                计划名称：{$plan['plan_name']}<br>
                计划类型：{$plan['plan_type_name']}<br>
                包含表数：{$plan['contents_count']}<br>
                状态：已启用
            ")
            ->refresh();

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        $templates = CleanupPlan::where('is_template', 1)->pluck('plan_name', 'id')->toArray();

        if (empty($templates)) {
            return [
                '暂无可用模板',
                '系统中暂无可用的计划模板，请先创建模板。',
            ];
        }

        return [
            '从模板创建计划',
            '请选择模板并输入新计划名称。',
            [
                'template_id' => [
                    'type' => 'select',
                    'label' => '选择模板',
                    'options' => $templates,
                    'required' => true,
                ],
                'plan_name' => [
                    'type' => 'text',
                    'label' => '计划名称',
                    'placeholder' => '请输入新计划名称',
                    'required' => true,
                ],
            ],
        ];
    }
}
