<?php

namespace DLaravel\Helper;

class PageList
{
    public int $current_page;

    public array $data;

    public int $last_page;

    public int $per_page;

    public int $total;

    public function __construct(\Illuminate\Pagination\LengthAwarePaginator $paginator)
    {
        $array = $paginator->toArray();

        $this->current_page = $array['current_page'];
        $this->data = $array['data'];
        $this->last_page = $array['last_page'];
        $this->per_page = $array['per_page'];
        $this->total = $array['total'];
    }

    public static function to(\Illuminate\Pagination\LengthAwarePaginator $paginato)
    {
        return new self($paginato);

    }
}
