<?php

namespace DLaravel\Random;

class IDCardNumber
{
    public function generateIdNumber()
    {
        // 随机生成地址码（前6位）
        $addressCode = str_pad(mt_rand(1, 10000), 6, '0', STR_PAD_LEFT);

        // 随机生成生日码（中间8位）
        $birthday = mt_rand(strtotime('1970-01-01'), time());
        $birthdayCode = date('Ymd', $birthday);

        // 随机生成顺序码（最后4位）
        $sequenceCode = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // 计算校验码
        $id17 = $addressCode.$birthdayCode.$sequenceCode;
        $checksum = 0;
        for ($i = 0; $i < 17; $i++) {
            $checksum += ($id17[$i] * (18 - $i));
        }
        $mod = $checksum % 11;
        $checkCode = $mod == 0 ? '1' : ($mod == 1 ? '0' : (12 - $mod) % 11);

        return $id17.$checkCode;
    }

    /**
     * 随机生成有效的 18 长度的身份证号
     * 中华人民共和国国家标准 GB/T 2260 行政区划代码 https://github.com/cn/GB2260.php/blob/master/README.md
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function genID()
    {
        // vendor/lybc/php-gb2260/data/cn-gb2260.php
        $addressCodes = require __DIR__.'/../../../vendor/lybc/php-gb2260/data/cn-gb2260.php'; // 地区编码
        $codes = array_keys($addressCodes);
        $code = $codes[array_rand($codes)];
        // 出生年月日
        $year = 1900 + mt_rand(50, 102); // 最大是 2002 年
        $month = str_pad(mt_rand(1, 12), 2, '0', STR_PAD_LEFT);
        switch ($month) {
            case '02':// 2 月按照 28 天算，简化一下，不算闰年
                $day = str_pad(mt_rand(1, 28), 2, '0', STR_PAD_LEFT);
                break;
            case '04':
            case '06':
            case '09':
            case '11':
                $day = str_pad(mt_rand(1, 30), 2, '0', STR_PAD_LEFT);
                break;
            default:
                $day = str_pad(mt_rand(1, 31), 2, '0', STR_PAD_LEFT);
                break;
        }

        $body = $code.$year.$month.$day.str_pad(mt_rand(1, 999), 3, '1'); // 身份证前 17 位
        // 十七位数字本体码加权求和公式 S = Sum(Ai * Wi), i = 0, … , 16 ，先对前17位数字的权求和
        // Ai:表示第i位置上的身份证号码数字值
        // Wi:表示第i位置上的加权因子 Wi: 7 9 10 5 8 4 2 1 6 3 7 9 10 5 8 4 2
        $weight = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2]; // 身份证的加权因子
        $bodySum = 0;
        // 累加body部分与位置加权的积
        for ($i = 0; $i < count($weight); $i++) {
            $bodySum = $bodySum + intval($body[$i]) * $weight[$i];
        }
        $checkNumber = null;
        $mod = $bodySum % 11; // 余数为 2 时即表示身份证最后一位为 X(表示 10)

        switch ($mod) {
            case 0:
                $checkNumber = '1';
                break;
            case 1:
                $checkNumber = '0';
                break;
            case 2:
                $checkNumber = 'X';
                break;
            case 3:
                $checkNumber = '9';
                break;
            case 4:
                $checkNumber = '8';
                break;
            case 5:
                $checkNumber = '7';
                break;
            case 6:
                $checkNumber = '6';
                break;
            case 7:
                $checkNumber = '5';
                break;
            case 8:
                $checkNumber = '4';
                break;
            case 9:
                $checkNumber = '3';
                break;
            case 10:
                $checkNumber = '2';
                break;
        }

        //        $sex = $body[16] % 2 == 1 ? '男' : '女';//第 17 为数字为奇数表示 男，否则为 女
        //        $address = (new \GB2260\GB2260())->get($code);//根据地区编码获取地址
        return $body.$checkNumber;
    }
}
