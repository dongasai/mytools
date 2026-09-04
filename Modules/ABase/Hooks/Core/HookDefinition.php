<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Core;

use InvalidArgumentException;

/**
 * Hook定义抽象类
 *
 * 所有Hook定义都应该继承此类，定义Hook的元数据、参数要求和返回值要求
 *
 * @property-read string $name Hook名称（使用类全名）
 * @property-read string $parameter_class 参数类名
 * @property-read string $return_class 返回值类名
 * @property-read string $description Hook描述
 */
abstract class HookDefinition
{
    /**
     * 参数类名（子类必须定义）
     * 必须继承自HookParameter
     */
    public readonly string $parameter_class;

    /**
     * 返回值类名（子类必须定义）
     * 必须继承自HookResult
     */
    public readonly string $return_class;

    /**
     * Hook描述
     */
    public string $description;

    /**
     * 是否为单处理器Hook
     *
     * 单处理器Hook在第一个处理器返回有效结果后即停止执行后续处理器
     * 适用于有明确数据提供者的场景，如用户列表查询、数据获取等
     */
    public bool $is_single_processor = false;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->validateDefinition();
    }

    /**
     * 验证Hook定义
     */
    protected function validateDefinition(): void
    {
        // 验证参数类
        if (! class_exists($this->parameter_class)) {
            throw new InvalidArgumentException("Parameter class '" . $this->parameter_class . "' does not exist");
        }

        if (! is_subclass_of($this->parameter_class, HookParameter::class)) {
            throw new InvalidArgumentException(
                "Parameter class '" . $this->parameter_class . "' must extend HookParameter"
            );
        }

        // 验证返回值类
        if (! class_exists($this->return_class)) {
            throw new InvalidArgumentException("Return class '" . $this->return_class . "' does not exist");
        }

        if ($this->return_class !== HookResult::class && ! is_subclass_of($this->return_class, HookResult::class)) {
            throw new InvalidArgumentException(
                "Return class '" . $this->return_class . "' must extend HookResult"
            );
        }
    }

    /**
     * 获取Hook名称
     * 使用类全名作为Hook名称
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * 获取参数类名
     */
    public function getParameterClass(): string
    {
        return $this->parameter_class;
    }

    /**
     * 获取返回值类名
     */
    public function getReturnClass(): string
    {
        return $this->return_class;
    }

    /**
     * 获取Hook描述
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * 设置Hook描述
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * 创建参数实例
     */
    public function createParameter(mixed ...$args): HookParameter
    {
        $parameterClass = $this->parameter_class;

        return new $parameterClass(...$args);
    }

    /**
     * 创建返回值实例
     */
    public function createResult(mixed ...$args): HookResult
    {
        $resultClass = $this->return_class;

        return new $resultClass(...$args);
    }

    /**
     * 创建成功结果
     *
     * 创建一个初始的成功结果对象，用于作为第一个处理器的输入
     * 不调用success()静态方法，因为不同Result类的签名可能不同
     */
    public function createSuccessResult(mixed $data = [], string $message = ''): HookResult
    {
        $resultClass = $this->return_class;

        // 直接实例化一个空的成功结果，让第一个处理器来填充具体数据
        // 使用反射检查构造函数参数
        try {
            $reflection = new \ReflectionClass($resultClass);
            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                // 无构造函数，直接实例化
                return new $resultClass();
            }

            // 分析构造函数参数
            $parameters = $constructor->getParameters();
            $args = [];

            foreach ($parameters as $param) {
                $paramName = $param->getName();
                $paramType = $param->getType();
                $hasDefault = $param->isDefaultValueAvailable();

                // 根据参数类型提供默认值
                if ($hasDefault) {
                    $args[] = $param->getDefaultValue();
                } elseif ($paramType && $paramType->getName() === 'bool') {
                    // bool类型默认为true（表示成功）
                    $args[] = true;
                } elseif ($paramType && $paramType->getName() === 'string') {
                    // string类型默认为空字符串或message
                    $args[] = $paramName === 'message' ? $message : '';
                } elseif ($paramType && $paramType->getName() === 'int') {
                    // int类型默认为0
                    $args[] = 0;
                } elseif ($paramType && $paramType->getName() === 'array') {
                    // array类型默认为空数组
                    $args[] = [];
                } elseif ($paramType && $paramType->getName() === 'mixed') {
                    // mixed类型默认为null或空值
                    $args[] = null;
                } else {
                    // 其他类型，尝试使用默认值或null
                    $args[] = $hasDefault ? $param->getDefaultValue() : null;
                }
            }

            return new $resultClass(...$args);
        } catch (\ReflectionException $e) {
            // 反射失败，尝试简单实例化
            return new $resultClass(true, $message);
        }
    }

    /**
     * 创建失败结果
     *
     * 创建一个初始的失败结果对象
     */
    public function createFailureResult(mixed $errors = [], string $message = ''): HookResult
    {
        $resultClass = $this->return_class;

        // 先创建一个基础结果对象
        try {
            $result = $this->createSuccessResult([], $message);
            // 设置为失败状态
            $result->setSuccess(false);
            $result->setMessage($message);

            // 如果提供了错误信息，添加到结果中
            if (is_array($errors) && ! empty($errors)) {
                foreach ($errors as $error) {
                    $result->addError((string) $error);
                }
            } elseif (is_string($errors) && ! empty($errors)) {
                $result->addError($errors);
            }

            return $result;
        } catch (\Exception $e) {
            // 如果创建失败，尝试直接实例化
            $result = new $resultClass(false, $message);

            if (is_array($errors) && ! empty($errors)) {
                foreach ($errors as $error) {
                    $result->addError((string) $error);
                }
            } elseif (is_string($errors) && ! empty($errors)) {
                $result->addError($errors);
            }

            return $result;
        }
    }

    /**
     * 验证参数是否匹配此Hook定义
     */
    public function validateParameter(HookParameter $parameter): bool
    {
        return $parameter instanceof $this->parameter_class;
    }

    /**
     * 验证返回值是否匹配此Hook定义
     */
    public function validateResult(HookResult $result): bool
    {
        return $result instanceof $this->return_class;
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'parameter_class' => $this->getParameterClass(),
            'return_class' => $this->getReturnClass(),
            'description' => $this->getDescription(),
            'class' => static::class,
        ];
    }

    /**
     * 转换为JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * 魔术方法：获取属性
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'name' => $this->getName(),
            'parameter_class' => $this->getParameterClass(),
            'return_class' => $this->getReturnClass(),
            'description' => $this->getDescription(),
            default => throw new InvalidArgumentException("Property '{$name}' does not exist"),
        };
    }

    /**
     * 魔术方法：转换为字符串
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * 检查Hook定义是否相等
     */
    public function equals(self $other): bool
    {
        return static::class === $other::class
            && $this->getParameterClass() === $other->getParameterClass()
            && $this->getReturnClass() === $other->getReturnClass();
    }

    /**
     * 获取Hook定义的唯一标识
     */
    public function getHash(): string
    {
        return md5(static::class);
    }

    /**
     * 检查是否为单处理器Hook
     */
    public function isSingleProcessor(): bool
    {
        return $this->is_single_processor;
    }

    /**
     * 验证单处理器Hook的结果是否有效
     *
     * @param  HookResult  $result  处理器返回的结果
     * @return bool  结果是否有效（有效则停止执行后续处理器）
     */
    public function isValidSingleProcessorResult(HookResult $result): bool
    {
        return $result->isProcessed();
    }

    /**
     * 获取单处理器配置信息
     */
    public function getSingleProcessorConfig(): array
    {
        return [
            'is_single_processor' => $this->is_single_processor,
        ];
    }
}
