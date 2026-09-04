<?php

namespace DLaravel\Helper;

class PageApp
{
    public int $current_page;

    public array $list;

    public int $last_page;

    public int $per_page;

    public int $total;

    public function __construct(\Illuminate\Pagination\LengthAwarePaginator $paginator)
    {
        $array = $paginator->toArray();
        //        dd($array);
        $this->current_page = $array['current_page'];
        $this->list = $array['data'];
        $this->last_page = $array['last_page'];
        $this->per_page = $array['per_page'];
        $this->total = $array['total'];

        $this->has_more = ($this->current_page < $this->last_page);
    }

    public static function to(\Illuminate\Pagination\LengthAwarePaginator $paginato)
    {
        return new self($paginato);

    }
}
