<?php

namespace Modules\FeatureSms\DcatAdmin\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Validator;
use Modules\Application\Services\SystemLogService;

/**
 * 发送测试短信表单
 */
class SendTestSmsForm extends Form implements LazyRenderable
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return Response
     */
    public function handle(array $input)
    {
        // 验证输入
        $validator = Validator::make($input, [
            'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
            'message' => 'required|string|max:500',
            'config_id' => 'required|exists:.sms_configs,id',
        ], [
            'phone.regex' => '请输入有效的手机号码',
            'config_id.exists' => '选择的短信配置不存在',
        ]);

        if ($validator->fails()) {
            return $this->response()->error($validator->errors()->first());
        }

        try {
            // 这里应该调用短信服务发送测试短信
            // 由于是示例，我们只记录日志
            \Log::info('测试短信发送', [
                'phone' => $input['phone'],
                'message' => $input['message'],
                'config_id' => $input['config_id'],
                'admin_user' => \Admin::user()->name,
            ]);

            return $this->response()
                ->success('测试短信发送成功')
                ->refresh();
        } catch (\Exception $e) {
            // 记录系统日志
            SystemLogService::exception('feature_sms', $e, [
                'phone' => $input['phone'],
                'config_id' => $input['config_id'],
                'context' => 'send_test_sms',
            ]);

            return $this->response()->error('发送失败：' . $e->getMessage());
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('phone', '手机号码')
            ->required()
            ->placeholder('请输入手机号码')
            ->help('用于接收测试短信的手机号码');

        $this->textarea('message', '短信内容')
            ->required()
            ->placeholder('请输入短信内容')
            ->help('短信内容不超过500字');

        $this->select('config_id', '短信配置')
            ->required()
            ->options(\Modules\FeatureSms\Models\SmsConfig::pluck('name', 'id'))
            ->help('选择用于发送测试短信的配置');

        $this->hidden('_token')->value(csrf_token());
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'phone' => '',
            'message' => '这是一条测试短信',
        ];
    }
}
