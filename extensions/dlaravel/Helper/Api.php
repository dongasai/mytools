<?php

namespace DLaravel\Helper;

class Api
{
    /**
     * API,错误返回
     *
     * @return false|string
     */
    public static function error($message = '错误', $code = 400, $data = [])
    {
        return static::return_json($code, $message, $data, false);
    }

    /**
     * 成功的返回
     *
     * @return false|string
     */
    public static function successData($data, $message = '成功', $code = 200)
    {
        return self::return_json($code, $message, $data, true);
    }

    /**
     * 进行res 数据判定返回
     *
     * @return false|string
     */
    public static function resData($data, $message = '成功', $code = 200)
    {
        if (is_string($data)) {
            return self::error($data);
        }

        return self::return_json($code, $message, $data, true);
    }

    /**
     * 返回验证错误
     *
     * @return void
     */
    public static function returnValidation(ValidationCore $validationCore, $msg = null)
    {
        $msg = $msg ?? $validationCore->firstError();

        return self::return_json(422, $msg, $validationCore->getSourceData(), false);
    }

    /**
     * 处理Laravel的验证错误
     *
     * @return false|string
     */
    public static function returnValidationException(\Illuminate\Validation\ValidationException $validationException)
    {
        return self::return_json(422, $validationException->getMessage(), $validationException->validator->getData(), false);
    }

    /**
     * 组织API返回json串
     *
     * @return false|string
     */
    public static function return_json($code = 200, $msg = '', $data = [], $success = true)
    {
        $json = json_encode([
            'success' => $success,
            'code' => $code,
            'message' => $msg,
            'data' => $data,
            'unid' => RUN_UNIQID,
        ]);

        return response($json);

    }

    /**
     * 多判断返回
     *
     * @return false|string|null
     */
    public static function returnRes($data, $msg = [])
    {
        if (is_string($data)) {
            return self::error($data, $msg);
        }
        if ($data instanceof ValidationCore) {
            return self::returnValidation($data);
        }

        return self::successData($data, $data);
    }
}
