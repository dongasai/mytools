<?php

namespace DLaravel\Validation;

use DLaravel\Exception\LogicException;
use DLaravel\Exception\ValidateException;
use DLaravel\Validator\ConsumeErrorValidator;
use DLaravel\Validator\ConsumeValidator;
use DLaravel\Validator\EnumValidator;

use Inhere\Validate\Validation;

/**
 * 验证核心类
 *
 * 提供验证基础功能，包括数据验证、类型转换、错误处理等
 */
abstract class ValidationCore extends Validation
{

    protected string $name = '';

    /**
     * 验证成功后的处理器列表
     *
     * @var ConsumeValidator[]
     */
    protected array $consumeValidators = [];

    /**
     * 验证失败后的处理器列表
     *
     * @var ConsumeErrorValidator[]
     */
    protected array $consumeErrorValidators = [];

    /**
     * 数值类型转换配置
     *
     * @var array<string, string>
     */
    protected array $cats = [];

    /**
     * 数据键名和默认值
     *
     * @return array<string, mixed>
     */
    public function default(): array
    {
        return [];
    }

    /**
     * 获取默认值的键名列表
     *
     * @return array<string>
     */
    public function keys(): array
    {
        $default = $this->default();

        return array_keys($default);
    }

    /**
     * 获取验证规则
     *
     * @param array $rules 自定义规则
     * @return array
     */
    public function rules(array $rules = []): array
    {
        return $this->callCatsRules($rules);
    }

    /**
     * 获取安全数据,使用默认值
     *
     * @param string|null $key 键名
     * @return mixed
     */
    public function getSafeByDefault(?string $key = null): mixed
    {
        $de = $this->default();
        if ($key === null) {
            $res = [];
            $keys = array_keys($de);
            foreach ($keys as $key) {
                $res[$key] = $this->getSafeByDefault($key);
            }

            return $res;
        }

        return $this->getSafe($key, $de[$key] ?? null);
    }

    /**
     * 获取原始数据
     *
     * @return array<string, mixed>
     */
    public function getSourceData(): array
    {
        return $this->data;
    }

    /**
     * 使用json数据 进行初始化
     *
     * @param string $scene 场景
     * @return static
     */
    public static function makeByJson(string $scene = ''): static
    {
        return new static(request()->json()->all(), [], [], $scene);
    }


    /**
     * 验证完成,不通过抛异常
     *
     * @return $this
     * @throws \DLaravel\Exception\ValidateException
     */
    public function validated(): static
    {
        $this->validate();

        if ($this->isFail()) {
            throw new ValidateException($this, $this->firstError());
        }

        return $this;
    }

    /**
     * 验证
     *
     * @param array $onlyChecked 只验证的字段
     * @param bool|null $stopOnError 是否在错误时停止
     * @return $this
     */
    public function validate(array $onlyChecked = [], ?bool $stopOnError = null): static
    {
        parent::validate($onlyChecked, $stopOnError);
        if ($this->isOk()) {
            $this->consume();
        }

        if ($this->isFail()) {
            $this->consumeError();
        }

        return $this;
    }

    /**
     * 验证完成,不通过抛异常
     *
     * @param bool $asObject 是否返回对象
     * @return array|object
     * @throws \DLaravel\Exception\ValidateException
     */
    public function validatedData(bool $asObject = false): array|object
    {
        $this->validated();

        return $this->getSafeData($asObject);
    }

    /**
     * 对需要消费的验证器进行消费
     *
     * @return void
     */
    public function consume(): void
    {
        foreach ($this->consumeValidators as $consumeValidator) {
            $consumeValidator->consume();
        }
    }

    /**
     * 对错误需要消费的验证器进行消费
     *
     * @return void
     */
    public function consumeError(): void
    {
        foreach ($this->consumeErrorValidators as $consumeValidator) {
            $consumeValidator->consumeError();
        }
    }

    /**
     * 增加一个正确需要消费的验证器
     *
     * @param ConsumeValidator $consumeValidator
     * @return static
     */
    public function addConsumeValidator(ConsumeValidator $consumeValidator): static
    {
        $this->consumeValidators[] = $consumeValidator;

        return $this;
    }

    /**
     * 增加一个错误需要消费的验证器
     *
     * @param ConsumeErrorValidator $consumeValidator
     * @return static
     */
    public function addConsumeErrorValidator(ConsumeErrorValidator $consumeValidator): static
    {
        $this->consumeErrorValidators[] = $consumeValidator;

        return $this;
    }

    /**
     * 是否存在安全键名
     *
     * @param string $key
     * @return bool
     */
    public function hasSafe(string $key): bool
    {
        $keys = $this->getSafeKeys();

        return in_array($key, $keys);
    }

    /**
     * 获取经过验证的数据
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getSafe(string $key, mixed $default = null): mixed
    {
        $value = parent::getSafe($key, $default);
        $cats = $this->cats[$key] ?? '';
        if ($cats) {
            return $this->getSafeCats($value, $cats);
        }

        return $value;
    }

    /**
     * 枚举结果
     *
     * @param mixed $value
     * @param string $enumClass
     * @return \BackedEnum|mixed
     * @throws LogicException
     */
    public function getSafeCats(mixed $value, string $enumClass): \BackedEnum
    {
        if (enum_exists($enumClass)) {
            return is_subclass_of($enumClass, \BackedEnum::class)
                ? $enumClass::from($value)
                : constant($enumClass . '::' . $value);
        }
        throw new LogicException("转义错误");
    }

    /**
     * 处理枚举的参数
     *
     * @param array $rules
     * @return array
     */
    protected function callCatsRules(array $rules): array
    {
        if ($this->cats) {
            foreach ($this->cats as $name => $class) {
                $rules[] = [
                    $name,
                    new EnumValidator($this, [$class])
                ];
            }
        }

        return $rules;
    }

    /**
     * 获取名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
