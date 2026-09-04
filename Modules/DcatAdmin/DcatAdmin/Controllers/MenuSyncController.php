<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Card;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\Services\MenuSyncService;
use Illuminate\Http\Request;

/**
 * 菜单同步管理控制器
 */
class MenuSyncController extends AdminController
{
    protected $title = '菜单同步管理';

    /**
     * 菜单同步状态仪表盘
     */
    public function index(Content $content): Content
    {
        $menuSyncService = app(MenuSyncService::class);
        $preview = $menuSyncService->previewSync();

        return $content
            ->title($this->title)
            ->description('配置文件菜单同步到数据库')
            ->body(function (Row $row) use ($preview) {
                // 第一行：统计卡片
                $row->column(4, $this->buildDatabaseStatsCard($preview['database_menus']));
                $row->column(4, $this->buildConfigStatsCard($preview['modules']));
                $row->column(4, $this->buildDifferenceStatsCard($preview['differences']));

                // 第二行：模块列表详情
                $row->column(12, $this->buildModulesDetailCard($preview['modules']));

                // 第三行：差异详情
                $row->column(12, $this->buildDifferenceDetailCard($preview['differences']));

                // 第四行：操作按钮
                $row->column(12, $this->buildActionButtonsCard());
            });
    }

    /**
     * 构建数据库菜单统计卡片
     */
    protected function buildDatabaseStatsCard(array $stats): Card
    {
        $html = '<table class="table table-striped">';
        $html .= '<tr><td>菜单总数</td><td><strong>' . $stats['total'] . '</strong></td></tr>';
        $html .= '<tr><td>父菜单数</td><td><strong>' . $stats['parents'] . '</strong></td></tr>';
        $html .= '<tr><td>子菜单数</td><td><strong>' . $stats['children'] . '</strong></td></tr>';
        $html .= '</table>';

        return Card::make('数据库菜单统计', $html);
    }

    /**
     * 构建配置文件统计卡片
     */
    protected function buildConfigStatsCard(array $modules): Card
    {
        $totalMenus = 0;
        $html = '<table class="table table-striped">';
        $html .= '<tr><th>模块</th><th>菜单数</th></tr>';

        foreach ($modules as $moduleName => $info) {
            $html .= '<tr>';
            $html .= '<td>' . $moduleName . '</td>';
            $html .= '<td><strong>' . $info['menu_count'] . '</strong></td>';
            $html .= '</tr>';
            $totalMenus += $info['menu_count'];
        }

        $html .= '<tr class="table-info"><td><strong>总计</strong></td><td><strong>' . $totalMenus . '</strong></td></tr>';
        $html .= '</table>';

        return Card::make('配置文件菜单统计', $html);
    }

    /**
     * 构建差异统计卡片
     */
    protected function buildDifferenceStatsCard(array $differences): Card
    {
        $toInsert = count($differences['to_insert']);
        $orphans = count($differences['orphans']);
        $conflicts = count($differences['conflicts']);

        $html = '<table class="table table-striped">';
        $html .= '<tr><td>待插入</td><td><strong class="text-success">' . $toInsert . '</strong></td></tr>';
        $html .= '<tr><td>孤儿菜单</td><td><strong class="text-danger">' . $orphans . '</strong></td></tr>';
        $html .= '<tr><td>URI冲突</td><td><strong class="text-danger">' . $conflicts . '</strong></td></tr>';
        $html .= '</table>';

        return Card::make('同步差异统计', $html);
    }

    /**
     * 构建模块详情卡片
     */
    protected function buildModulesDetailCard(array $modules): Card
    {
        $html = '<table class="table table-hover">';
        $html .= '<thead><tr>';
        $html .= '<th>模块名</th><th>配置键</th><th>菜单数量</th><th>操作</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($modules as $moduleName => $info) {
            $syncUrl = route('dcat.admin.menu-sync.sync', ['module' => $moduleName]);
            $html .= '<tr>';
            $html .= '<td>' . $moduleName . '</td>';
            $html .= '<td><code>' . $info['config_key'] . '</code></td>';
            $html .= '<td>' . $info['menu_count'] . '</td>';
            $html .= '<td>';
            $html .= '<a href="' . $syncUrl . '" class="btn btn-sm btn-primary">同步</a> ';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return Card::make('模块列表详情', $html);
    }

    /**
     * 构建差异详情卡片
     */
    protected function buildDifferenceDetailCard(array $differences): Card
    {
        $html = '';

        // 待插入菜单
        if (!empty($differences['to_insert'])) {
            $html .= '<h5 class="text-success">待插入菜单</h5>';
            $html .= '<table class="table table-sm table-striped">';
            $html .= '<thead><tr><th>模块</th><th>ID</th><th>标题</th><th>URI</th></tr></thead><tbody>';

            foreach ($differences['to_insert'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . $item['module'] . '</td>';
                $html .= '<td>' . $item['menu']['id'] . '</td>';
                $html .= '<td>' . $item['menu']['title'] . '</td>';
                $html .= '<td>' . ($item['menu']['uri'] ?? '') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table><br>';
        }

        // 孤儿菜单
        if (!empty($differences['orphans'])) {
            $html .= '<h5 class="text-danger">孤儿菜单（数据库存在但配置文件不存在）</h5>';
            $html .= '<p>菜单ID: <code>' . implode(', ', $differences['orphans']) . '</code></p><br>';
        }

        // URI冲突
        if (!empty($differences['conflicts'])) {
            $html .= '<h5 class="text-danger">URI冲突</h5>';
            $html .= '<table class="table table-sm table-striped">';
            $html .= '<thead><tr><th>URI</th><th>冲突模块</th><th>消息</th></tr></thead><tbody>';

            foreach ($differences['conflicts'] as $conflict) {
                $html .= '<tr>';
                $html .= '<td><code>' . $conflict['uri'] . '</code></td>';
                $html .= '<td>' . implode(', ', $conflict['modules']) . '</td>';
                $html .= '<td>' . $conflict['message'] . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        if (empty($html)) {
            $html = '<div class="alert alert-success">所有配置文件菜单已存在于数据库，无需同步</div>';
        }

        return Card::make('同步差异详情', $html);
    }

    /**
     * 构建操作按钮卡片
     */
    protected function buildActionButtonsCard(): Card
    {
        $html = '<div class="row">';

        // 同步所有模块
        $syncAllUrl = route('dcat.admin.menu-sync.sync');
        $html .= '<div class="col-md-4">';
        $html .= '<a href="' . $syncAllUrl . '" class="btn btn-primary btn-block btn-lg">';
        $html .= '<i class="feather icon-refresh-cw"></i> 同步所有模块';
        $html .= '</a>';
        $html .= '</div>';

        // 删除孤儿菜单
        $deleteOrphanUrl = route('dcat.admin.menu-sync.sync', ['delete_orphan' => 1]);
        $html .= '<div class="col-md-4">';
        $html .= '<a href="' . $deleteOrphanUrl . '" class="btn btn-danger btn-block btn-lg" onclick="return confirm(\'确定要删除孤儿菜单吗？\')">';
        $html .= '<i class="feather icon-trash-2"></i> 同步并删除孤儿';
        $html .= '</a>';
        $html .= '</div>';

        // 预览模式
        $previewUrl = route('dcat.admin.menu-sync.index');
        $html .= '<div class="col-md-4">';
        $html .= '<a href="' . $previewUrl . '" class="btn btn-secondary btn-block btn-lg">';
        $html .= '<i class="feather icon-eye"></i> 刷新预览';
        $html .= '</a>';
        $html .= '</div>';

        $html .= '</div>';

        return Card::make('同步操作', $html);
    }

    /**
     * 执行菜单同步
     */
    public function sync(Request $request, Content $content): Content
    {
        $module = $request->get('module');
        $deleteOrphan = $request->get('delete_orphan', false);

        $menuSyncService = app(MenuSyncService::class);

        // 执行同步
        if ($module) {
            $result = $menuSyncService->syncModule($module);
        } else {
            $result = $menuSyncService->syncAll($deleteOrphan);
        }

        return $content
            ->title('同步结果')
            ->description($module ? "模块 {$module} 同步完成" : '所有模块同步完成')
            ->body(function (Row $row) use ($result, $module) {
                // 统计摘要
                $row->column(12, $this->buildResultSummaryCard($result));

                // 详细结果
                if (!$module && isset($result['details'])) {
                    foreach ($result['details'] as $moduleName => $moduleResult) {
                        $row->column(12, $this->buildModuleResultCard($moduleName, $moduleResult));
                    }
                } elseif ($module) {
                    $row->column(12, $this->buildModuleResultCard($module, $result));
                }

                // 错误详情
                if (!empty($result['errors_details'])) {
                    $row->column(12, $this->buildErrorDetailCard($result['errors_details']));
                }

                // 返回按钮
                $row->column(12, $this->buildBackButtonCard());
            });
    }

    /**
     * 构建返回按钮卡片
     */
    protected function buildBackButtonCard(): Card
    {
        $backUrl = route('dcat.admin.menu-sync.index');
        $html = '<a href="' . $backUrl . '" class="btn btn-primary">';
        $html .= '<i class="feather icon-arrow-left"></i> 返回同步管理';
        $html .= '</a>';
        return Card::make('操作', $html);
    }

    /**
     * 构建结果摘要卡片
     */
    protected function buildResultSummaryCard(array $result): Card
    {
        $html = '<div class="row">';

        $cards = [
            ['title' => '扫描模块', 'value' => $result['modules_scanned'], 'class' => 'info'],
            ['title' => '菜单总数', 'value' => $result['total_menu_items'], 'class' => 'info'],
            ['title' => '插入成功', 'value' => $result['inserted'], 'class' => 'success'],
            ['title' => '跳过', 'value' => $result['skipped'], 'class' => 'secondary'],
            ['title' => '错误', 'value' => $result['errors'], 'class' => 'danger'],
            ['title' => '孤儿菜单', 'value' => $result['orphans_found'], 'class' => 'warning'],
            ['title' => '删除孤儿', 'value' => $result['orphans_deleted'], 'class' => 'danger'],
        ];

        foreach ($cards as $card) {
            $html .= '<div class="col-md-3">';
            $html .= '<div class="small-box bg-' . $card['class'] . '">';
            $html .= '<div class="inner"><h3>' . $card['value'] . '</h3><p>' . $card['title'] . '</p></div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        // 执行时间
        $html .= '<p class="text-muted">执行时间: ' . $result['duration'] . ' 秒</p>';

        return Card::make('同步结果摘要', $html);
    }

    /**
     * 构建模块结果卡片
     */
    protected function buildModuleResultCard(string $moduleName, array $moduleResult): Card
    {
        $html = '<h5>' . $moduleName . ' 模块</h5>';
        $html .= '<table class="table table-sm table-striped">';
        $html .= '<thead><tr><th>状态</th><th>数量</th></tr></thead><tbody>';
        $html .= '<tr><td>菜单总数</td><td>' . $moduleResult['total_items'] . '</td></tr>';
        $html .= '<tr class="table-success"><td>插入成功</td><td>' . $moduleResult['inserted'] . '</td></tr>';
        $html .= '<tr class="table-secondary"><td>跳过</td><td>' . $moduleResult['skipped'] . '</td></tr>';
        $html .= '<tr class="table-danger"><td>错误</td><td>' . $moduleResult['errors'] . '</td></tr>';
        $html .= '</tbody></table>';

        // 详细操作列表
        if (!empty($moduleResult['items'])) {
            $html .= '<h6>操作详情</h6>';
            $html .= '<table class="table table-sm">';
            $html .= '<thead><tr><th>ID</th><th>标题</th><th>URI</th><th>状态</th><th>消息</th></tr></thead><tbody>';

            foreach ($moduleResult['items'] as $item) {
                $statusClass = $item['status'] === 'error' ? 'table-danger' :
                    ($item['status'] === 'inserted' ? 'table-success' : '');

                $html .= '<tr class="' . $statusClass . '">';
                $html .= '<td>' . $item['menu_id'] . '</td>';
                $html .= '<td>' . $item['title'] . '</td>';
                $html .= '<td>' . ($item['uri'] ?? '-') . '</td>';
                $html .= '<td><strong>' . $item['status'] . '</strong></td>';
                $html .= '<td>' . $item['message'] . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        return Card::make('', $html);
    }

    /**
     * 构建错误详情卡片
     */
    protected function buildErrorDetailCard(array $errors): Card
    {
        $html = '<div class="alert alert-danger"><h5>错误详情</h5></div>';

        if (!empty($errors['uri_conflicts'])) {
            $html .= '<h6>URI冲突</h6>';
            $html .= '<table class="table table-sm table-striped">';
            $html .= '<thead><tr><th>URI</th><th>冲突模块</th><th>消息</th></tr></thead><tbody>';

            foreach ($errors['uri_conflicts'] as $conflict) {
                $html .= '<tr>';
                $html .= '<td><code>' . $conflict['uri'] . '</code></td>';
                $html .= '<td>' . implode(', ', $conflict['modules']) . '</td>';
                $html .= '<td>' . $conflict['message'] . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        if (!empty($errors['exception'])) {
            $html .= '<h6>异常信息</h6>';
            $html .= '<div class="alert alert-danger">';
            $html .= '<p><strong>消息:</strong> ' . $errors['exception']['message'] . '</p>';
            $html .= '<p><strong>文件:</strong> ' . $errors['exception']['file'] . '</p>';
            $html .= '<p><strong>行号:</strong> ' . $errors['exception']['line'] . '</p>';
            $html .= '</div>';
        }

        return Card::make('', $html);
    }
}