<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Widgets\Markdown;

class ReadmeController extends Controller
{
    public function index(Content $content, $path)
    {

        $pp = base_path('Readme/'.$path.'.md');
        $con = file_get_contents($pp);

        return $content->body(Card::make(
            Markdown::make($con)
        ));
    }
}
