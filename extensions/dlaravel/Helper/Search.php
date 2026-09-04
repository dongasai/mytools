<?php

namespace DLaravel\Helper;

class Search
{
    /**
     * 搜索关键词
     *
     * @return array
     */
    public static function query($model, $q)
    {
        return Cache::cacheCall([__CLASS__, __FILE__, $model, $q], function ($model, $search) {
            return $model::search($search)->paginate(9999, 'a', 1)->pluck('id');
        }, [$model, $q], 1);
    }

    public static function queryIndex2Id($model, $q)
    {
        return Cache::cacheCall([__CLASS__, __FILE__, $model, $q], function ($model, $search) {

            /**
             * @var \Illuminate\Pagination\Paginator $res
             */
            $res = $model::search($search)->simplePaginateRaw(9999, 'a', 1);

            $ids = array_column($res->items()['hits'], 'id');

            //            dump($ids);
            //             dd($res->items(),$ids);
            return $ids;
        }, [$model, $q], 1);
    }
}
