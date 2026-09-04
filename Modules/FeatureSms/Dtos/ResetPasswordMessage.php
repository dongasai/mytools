<?php

namespace Modules\FeatureSms\Dtos;

use Modules\FeatureSms\Services\MyGateway;
use DLaravel\Helper\Str;
use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Gateways\SmsbaoGateway;

/**
 * 重置密码验证码消息
 */
class ResetPasswordMessage extends BaseMessage
{
    /**
     * 获取消息内容
     *
     * @param  GatewayInterface|null  $gateway  短信网关
     */
    public function getContent(?GatewayInterface $gateway = null): string
    {
        if (is_null($gateway)) {
            return '';
        }

        $template = match (true) {
            $gateway instanceof MyGateway => '重置密码验证码:{code}',
            $gateway instanceof SmsbaoGateway => $gateway->getConfig()['reset_password_template'] ?? '重置密码验证码:{code}',
            default => ''
        };

        return Str::strtr($template, $this->getData($gateway));
    }

    public function getTemplate(?GatewayInterface $gateway = null): ?string
    {
        return 'SMS_RESET_PASSWORD';
    }

    public function getData(?GatewayInterface $gateway = null): array
    {
        return [
            'code' => $this->data['code'],
        ];
    }
}
