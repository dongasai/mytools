<?php

namespace Modules\FeatureSms\Services;

use Carbon\Carbon;
use DLaravel\Exception\LogicException;
use DLaravel\Helper\Logger;
use Modules\Application\Services\SystemLogService;
use Modules\FeatureSms\Enums\Code;
use Modules\FeatureSms\Enums\CODE_TYPE;
use Modules\FeatureSms\Models\SmsCode;
use Modules\FeatureSms\Models\SmsConfig;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\InvalidArgumentException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

/**
 * 短信服务类
 */
class SmsService
{
    /**
     * 验证码有效期（秒）
     */
    public const CODE_EXPIRE_TIME = 600;

    /**
     * @var EasySms
     */
    protected $easySms;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->initEasySms();
    }

    /**
     * 初始化 EasySms 实例
     */
    protected function initEasySms(): void
    {
        // 从 SmsConfig 模型读取配置
        $configs = SmsConfig::query()
            ->where('is_open', true)
            ->get();

        $gateways = [];
        $gatewayConfigs = [];

        foreach ($configs as $configModel) {
            $driver = $configModel->driver;

            // 解析 JSON 格式的配置内容
            $configData = json_decode($configModel->value, true) ?? [];

            // 构建网关配置
            if ($driver === 'db') {
                // db 驱动映射到自定义 MyGateway
                $gateways[] = 'mygateway';
                $gatewayConfigs['mygateway'] = [
                    'gateway' => MyGateway::class,
                ];
            } else {
                // 其他驱动直接使用配置数据
                $gateways[] = $driver;
                $gatewayConfigs[$driver] = $configData;
            }
        }

        // 获取基础配置
        $config = config('easysms');
        $config['default']['gateways'] = $gateways;
        $config['gateways'] = array_merge($config['gateways'] ?? [], $gatewayConfigs);

        // 创建实例
        $this->easySms = new EasySms($config);

        // 注册自定义网关
        $this->easySms->extend('mygateway', function ($gatewayConfig) {
            return new MyGateway($gatewayConfig);
        });
    }

    /**
     * 发送验证码
     *
     * @param  CODE_TYPE  $type  验证码类型
     * @param  string  $phone  手机号
     * @param  string  $token  令牌
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws NoGatewayAvailableException
     */
    public function sendCode(CODE_TYPE $type, string $phone, string $token): bool
    {
        // 验证类型
        if (! Code::isValid($type)) {
            throw new LogicException('不存在的验证码类型');
        }

        // 生成验证码
        $code = mt_rand(100000, 999999);

        // 获取消息类
        $messageClass = Code::getTemplateClass($type);
        if (! $messageClass) {
            throw new LogicException('未找到对应的消息模板');
        }

        // 发送短信
        try {
            $result = $this->easySms->send($phone, new $messageClass([
                'code' => $code,
                'phone' => $phone,
            ]));
        } catch (NoGatewayAvailableException $e) {
            // 记录短信 API 调用失败
            SystemLogService::exception('feature_sms', $e, [
                'phone' => $phone,
                'type' => $type->value,
                'gateway' => $e->getLastException() ? $e->getLastException()->getMessage() : 'unknown',
            ]);
            throw $e;
        } catch (InvalidArgumentException $e) {
            // 记录配置错误
            SystemLogService::exception('feature_sms', $e, [
                'phone' => $phone,
                'type' => $type->value,
            ]);
            throw $e;
        }

        // 记录日志
        Logger::debug('sms.send', $result);

        // 保存验证码
        try {
            $smsCode = new SmsCode;
            $smsCode->mobile = $phone;
            $smsCode->token = $token;
            $smsCode->code_value = $code;
            $smsCode->type = $type->value;
            $smsCode->sent_at = Carbon::now();
            $smsCode->save();
        } catch (\Throwable $e) {
            // 记录数据库操作失败
            SystemLogService::exception('feature_sms', $e, [
                'phone' => $phone,
                'type' => $type->value,
                'context' => 'save_sms_code',
            ]);
            throw $e;
        }

        return true;
    }

    /**
     * 验证短信验证码
     *
     * @param  CODE_TYPE|int  $type  验证码类型
     * @param  string  $phone  手机号
     * @param  string  $code  验证码
     */
    public function verifyCode(CODE_TYPE|int $type, string $phone, string $code): bool
    {
        if (empty($phone) || empty($code)) {
            return false;
        }

        $typeValue = $type instanceof CODE_TYPE ? $type->value : $type;

        // 获取最新的验证码记录
        $smsCode = SmsCode::query()
            ->where('mobile', $phone)
            ->where('type', $typeValue)
            ->orderByDesc('id')
            ->first();

        if (! $smsCode) {
            return false;
        }

        // 检查验证码是否过期
        if ($smsCode->created_at->lt(Carbon::now()->subSeconds(self::CODE_EXPIRE_TIME))) {
            $smsCode->forceDelete();

            return false;
        }

        // 验证码匹配检查
        if ($smsCode->code_value === $code) {
            $smsCode->forceDelete();

            return true;
        }

        return false;
    }
}
