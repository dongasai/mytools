<?php

namespace DLaravel\Enum;

trait EnumCore
{
    /**
     * 获取常量注释
     *
     * @param  string  $key  常量名
     */
    public static function getDescription(string $key): string
    {
        return preg_replace('#[\*\s]*(^/|/$)[\*\s]*#', '', (new \ReflectionClassConstant(static::class, $key))->getDocComment());
    }

    /**
     * 获取 备注/描述
     */
    public function getDesc(): string
    {
        $key = $this->name;

        return preg_replace('#[\*\s]*(^/|/$)[\*\s]*#', '', (new \ReflectionClassConstant(static::class, $key))->getDocComment());
    }

    /**
     * 获取常量名和注释列表
     */
    public static function getKeyDescription(): array
    {
        $keys = self::cases();
        //        dump($keys);
        $result = [];

        foreach ($keys as $key => $key_name) {
            $result[$key_name->name] = self::getDescription($key_name->name);
        }

        return $result;
    }

    /**
     * 获取描述=>数值
     */
    public static function getDescriptionValue(): array
    {
        $kd = self::getKeyDescription();
        $arr = self::toArray();
        $res = [];
        foreach ($arr as $key => $value) {
            $res[$kd[$key]] = $value;
        }

        return $res;

    }

    /**
     * 获取数值=>描述
     */
    public static function getValueDescription(): array
    {
        $kd = self::getKeyDescription();
        $arr = self::toArray();
        //        dd($kd,$arr);

        $res = [];
        foreach ($arr as $key => $value) {
            $res[$value] = $kd[$key];
        }

        return $res;

    }

    public static function toArray()
    {
        $keys = self::cases();
        $res = [];
        foreach ($keys as $key) {
            $res[$key->name] = $key->value();
        }

        return $res;
    }

    public static function keys()
    {
        $keys = self::cases();
        $res = [];
        foreach ($keys as $key) {
            $res[] = $key->name;
        }

        return $res;
    }
}
