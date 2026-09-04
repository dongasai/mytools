<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use App\Http\Controllers\Controller;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Modules\Application\Admin\Repositories\Logs;

class LogsController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('日志查看')
            ->body($this->logs());
    }

    public function index2(Content $content)
    {
        return $content
            ->title('日志查看-cli')
            ->body($this->logs2());
    }

    public function indexCron(Content $content)
    {
        return $content
            ->title('日志查看-Cron')
            ->body($this->logsCron());
    }

    public function logs()
    {
        return Grid::make(new Logs, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('content');

            $grid->disableActions(true);
            $grid->disableBatchActions(true);
            $grid->disableToolbar(true);
            $grid->disablePagination();
        });
    }

    public function logs2()
    {
        $logs = new Logs;

        return Grid::make($logs->getCliLogs(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('content');

            $grid->disableActions(true);
            $grid->disableBatchActions(true);
            $grid->disableToolbar(true);
            $grid->disablePagination();
        });
    }

    public function logsCron()
    {
        $logs = new Logs;

        return Grid::make($logs->getCronLogs(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('content');

            $grid->disableActions(true);
            $grid->disableBatchActions(true);
            $grid->disableToolbar(true);
            $grid->disablePagination();
        });
    }
}
