<?php

namespace DLaravel\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Serializable;

/**
 * DTO基类
 *
 * 为所有DTO提供基础功能，包括：
 * 1. 数组转换
 * 2. JSON序列化
 * 3. 从数组创建对象
 * 4. PHP序列化支持，便于缓存
 * 不要在构造函数定义属性
 */
abstract class BaseDto implements Arrayable, Jsonable, JsonSerializable, Serializable
{
    /**
     * 从数组创建DTO对象
     *
     * 子类可以覆盖此方法以提供自定义的创建逻辑
     *
     * @param  array  $data  数据数组
     */
    public static function fromArray(array $data): static
    {
        $dto = new static;

        foreach ($data as $key => $value) {
            // 转换下划线命名为驼峰命名
            $property = static::snakeToCamel($key);

            // 如果属性存在，则设置值
            if (property_exists($dto, $property)) {
                $dto->{$property} = $value;
            }
        }

        return $dto;
    }

    /**
     * 转换为数组
     *
     * 将DTO对象的所有公共属性转换为数组
     */
    public function toArray(): array
    {
        $result = [];
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->{$name};

            // 转换驼峰命名为下划线命名
            $key = static::camelToSnake($name);

            // 处理嵌套的DTO对象或DTO数组
            if ($value instanceof Arrayable) {
                $result[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $result[$key] = $this->processArrayValues($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * 处理数组中的值
     *
     * 递归处理数组中的DTO对象
     *
     * @param  array  $array  要处理的数组
     * @return array 处理后的数组
     */
    protected function processArrayValues(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if ($value instanceof Arrayable) {
                $result[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $result[$key] = $this->processArrayValues($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * JSON序列化
     *
     * 实现JsonSerializable接口，使DTO对象可以直接被json_encode()处理
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 转换为JSON字符串
     *
     * @param  int  $options  JSON编码选项
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * 将下划线命名转换为驼峰命名
     *
     * @param  string  $input  输入字符串
     * @return string 驼峰命名的字符串
     */
    public static function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }

    /**
     * 将驼峰命名转换为下划线命名
     *
     * @param  string  $input  输入字符串
     * @return string 下划线命名的字符串
     */
    protected static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * 创建一个新的DTO实例
     *
     * @param  array  $data  初始数据
     */
    public static function make(array $data = []): static
    {
        return static::fromArray($data);
    }

    /**
     * 将多个数组转换为DTO对象数组
     *
     * @param  array  $items  数据数组的数组
     * @return array DTO对象数组
     */
    public static function collection(array $items): array
    {
        return array_map(function ($item) {
            if ($item instanceof static) {
                return $item;
            }

            return static::fromArray($item);
        }, $items);
    }

    /**
     * 从缓存中恢复对象数组
     *
     * 当从缓存中获取对象数组时，可能会得到序列化的对象或数组
     * 该方法确保返回的是正确类型的DTO对象数组
     *
     * @param  mixed  $cachedData  缓存的数据
     * @return array DTO对象数组
     */
    public static function fromCache($cachedData): array
    {
        if (is_null($cachedData)) {
            return [];
        }

        if (! is_array($cachedData)) {
            return [];
        }

        $result = [];

        foreach ($cachedData as $key => $item) {
            if ($item instanceof static) {
                $result[$key] = $item;
            } elseif (is_array($item)) {
                $result[$key] = static::fromArray($item);
            }
        }

        return $result;
    }

    /**
     * 序列化对象
     *
     * 实现Serializable接口，使对象可以被序列化存储
     *
     * @return string 序列化后的字符串
     */
    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    /**
     * 反序列化对象
     *
     * 实现Serializable接口，从序列化字符串创建对象
     *
     * @param  string  $data  序列化的字符串
     */
    public function unserialize(string $data): void
    {
        $values = unserialize($data);

        foreach ($values as $key => $value) {
            // 转换下划线命名为驼峰命名
            $property = static::snakeToCamel($key);

            // 如果属性存在，则设置值
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * 从对象创建新实例
     *
     * 用于快速复制DTO对象
     *
     * @return static 新的DTO实例
     */
    public function clone(): static
    {
        return static::fromArray($this->toArray());
    }

    /**
     * 检查对象是否为空
     *
     * 如果所有属性都为默认值或空值，则认为对象为空
     *
     * @return bool 对象是否为空
     */
    public function isEmpty(): bool
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->{$name};

            // 检查属性值是否为空
            if (is_string($value) && $value !== '') {
                return false;
            } elseif (is_numeric($value) && $value !== 0) {
                return false;
            } elseif (is_array($value) && ! empty($value)) {
                return false;
            } elseif (is_object($value) && $value instanceof self && ! $value->isEmpty()) {
                return false;
            } elseif (! is_null($value) && ! is_string($value) && ! is_numeric($value) && ! is_array($value) && ! is_object($value)) {
                return false;
            }
        }

        return true;
    }
}
