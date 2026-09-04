<?php

namespace Modules\Application\DcatAdmin\Repositories;

use Dcat\Admin\Grid;
use Dcat\Admin\Repositories\Repository;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;

/**
 * 日志读取
 */
class Logs extends Repository
{
    /**
     * 合并LogsCron功能 - 读取定时任务日志
     */
    public function getCronLogs()
    {
        $path = storage_path('logs/cron.log');

        return self::read($path);
    }

    /**
     * 合并Logs2功能 - 读取CLI日志
     */
    public function getCliLogs()
    {
        $path = storage_path('logs/cli.log');

        return self::read($path);
    }

    public function get(Grid\Model $model)
    {
        $list = [];
        /**
         * @var \Monolog\Logger $loger
         */
        $loger = Log::getLogger();
        /**
         * @var \Monolog\Handler\StreamHandler $handler
         */
        $handler = $loger->getHandlers()[0];
        if ($handler instanceof StreamHandler) {
            $file = $handler->getUrl();
            $list = self::read($file, 100);
            //            dump($list);
        }

        return $list;
    }

    public static function read($file, $lineNumber = 30)
    {

        $f = fopen($file, 'r');

        $cursor = -1;

        fseek($f, $cursor, SEEK_END);

        $char = fgetc($f);

        /**
         * Trim trailing newline chars of the file
         * 修剪文件的尾部换行符
         */
        while ($char === "\n" || $char === "\r") {

            fseek($f, $cursor--, SEEK_END);

            $char = fgetc($f);

        }

        $res = [];
        foreach (range(0, $lineNumber) as $a) {
            $line = '';

            /**
             * Read until the start of file or first newline char
             * 读取到文件开头或第一个换行符
             */
            while ($char !== false && $char !== "\n" && $char !== "\r") {
                //                dump($char,$cursor);
                /**
                 * Prepend the new char
                 * 前置新的char
                 */
                $line = $char.$line;

                fseek($f, $cursor--, SEEK_END);

                $char = fgetc($f);
                //                dump($char);

            }
            //            $cursor--;
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);

            $res[] = [
                'id' => $a,
                'content' => $line,
            ];
        }

        return $res;
    }
}
