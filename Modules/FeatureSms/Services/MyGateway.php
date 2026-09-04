<?php

namespace Modules\FeatureSms\Services;

use Modules\Application\Services\SystemLogService;
use Modules\FeatureSms\Models\SmsDbGateway;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Gateways\Gateway;

class MyGateway extends Gateway
{
    /**
     * 发送短信并记录日志
     *
     * @param  PhoneNumberInterface  $to  手机号
     * @param  MessageInterface  $message  消息内容
     * @param  array  $config  配置信息
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, $config): array
    {
        try {
            // 记录发送日志
            $log = new SmsDbGateway;
            $log->mobile = $to->getNumber();
            $log->idd_code = $to->getIDDCode() ?? '86'; // 默认中国区号
            $log->zero_prefixed_number = $to->getZeroPrefixedNumber() ?? '';
            $log->universal_number = $to->getUniversalNumber() ?? '';
            $log->tpl_id = $message->getTemplate($this);
            $log->tpl_value = json_encode($message->getData($this), JSON_UNESCAPED_UNICODE);
            $log->content = $message->getContent($this);
            $log->key = uniqid('sms_', true); // 生成唯一键
            $log->save();

            return [
                'id' => $log->id,
                'key' => $log->key,
            ];
        } catch (\Throwable $e) {
            // 记录数据库操作失败
            SystemLogService::exception('feature_sms', $e, [
                'phone' => $to->getNumber(),
                'context' => 'sms_db_gateway_save',
            ]);
            throw $e;
        }
    }
}
