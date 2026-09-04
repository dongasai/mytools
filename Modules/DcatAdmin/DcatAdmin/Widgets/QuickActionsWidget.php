<?php

namespace Modules\DcatAdmin\DcatAdmin\Widgets;

use Dcat\Admin\Widgets\Card;

/**
 * 快捷操作小部件
 */
class QuickActionsWidget extends Card
{
    public function __construct()
    {
        parent::__construct('快捷操作', $this->buildContent());
    }

    /**
     * 构建内容
     */
    protected function buildContent(): string
    {
        $actions = [
            [
                'title' => '清理缓存',
                'icon' => 'fa-broom',
                'color' => 'warning',
                'url' => admin_url('admin-cache'),
                'description' => '清理系统缓存',
            ],
            [
                'title' => '系统信息',
                'icon' => 'fa-info-circle',
                'color' => 'info',
                'url' => admin_url('system-info'),
                'description' => '查看系统详细信息',
            ],
            [
                'title' => '日志管理',
                'icon' => 'fa-file-alt',
                'color' => 'primary',
                'url' => admin_url('logs'),
                'description' => '查看和管理系统日志',
            ],
            [
                'title' => '系统维护',
                'icon' => 'fa-tools',
                'color' => 'danger',
                'url' => '#',
                'description' => '执行系统维护操作',
                'onclick' => 'performMaintenance()',
            ],
            [
                'title' => '备份系统',
                'icon' => 'fa-archive',
                'color' => 'success',
                'url' => '#',
                'description' => '创建系统备份',
                'onclick' => 'createBackup()',
            ],
            [
                'title' => '性能监控',
                'icon' => 'fa-chart-line',
                'color' => 'secondary',
                'url' => admin_url('performance'),
                'description' => '查看性能监控数据',
            ],
        ];

        $html = '<div class="list-group list-group-flush">';

        foreach ($actions as $action) {
            $onclick = isset($action['onclick']) ? "onclick=\"{$action['onclick']}\"" : '';
            $href = isset($action['onclick']) ? 'javascript:void(0)' : $action['url'];

            $html .= '<a href="'.$href.'" class="list-group-item list-group-item-action" '.$onclick.'>';
            $html .= '<div class="d-flex w-100 justify-content-between align-items-center">';
            $html .= '<div>';
            $html .= "<i class=\"fa {$action['icon']} text-{$action['color']} mr-2\"></i>";
            $html .= "<strong>{$action['title']}</strong>";
            $html .= "<br><small class=\"text-muted\">{$action['description']}</small>";
            $html .= '</div>';
            $html .= '<i class="fa fa-chevron-right text-muted"></i>';
            $html .= '</div>';
            $html .= '</a>';
        }

        $html .= '</div>';

        // 添加JavaScript函数
        $html .= $this->getQuickActionsScript();

        return $html;
    }

    /**
     * 获取快捷操作JavaScript
     */
    protected function getQuickActionsScript(): string
    {
        $maintenanceUrl = admin_url('api/maintenance');
        $backupUrl = admin_url('api/backup');

        return "
        <script>
        function performMaintenance() {
            if (confirm(\"确定要执行系统维护吗？这可能需要几分钟时间。\")) {
                Dcat.loading();

                $.post(\"{$maintenanceUrl}\", {
                    _token: $(\"meta[name=csrf-token]\").attr(\"content\"),
                    clear_cache: true,
                    clean_logs: false,
                    optimize_database: false
                }).done(function(data) {
                    Dcat.loading(false);
                    if (data.status === \"success\") {
                        Dcat.success(\"系统维护完成\");
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        Dcat.error(data.message || \"维护失败\");
                    }
                }).fail(function() {
                    Dcat.loading(false);
                    Dcat.error(\"维护操作失败\");
                });
            }
        }

        function createBackup() {
            if (confirm(\"确定要创建系统备份吗？\")) {
                Dcat.loading();

                $.post(\"{$backupUrl}\", {
                    _token: $(\"meta[name=csrf-token]\").attr(\"content\"),
                    include_database: true,
                    include_files: false,
                    include_config: true
                }).done(function(data) {
                    Dcat.loading(false);
                    if (data.status === \"success\") {
                        Dcat.success(\"备份创建成功\");
                        if (data.download_url) {
                            window.open(data.download_url, \"_blank\");
                        }
                    } else {
                        Dcat.error(data.message || \"备份失败\");
                    }
                }).fail(function() {
                    Dcat.loading(false);
                    Dcat.error(\"备份操作失败\");
                });
            }
        }
        </script>";
    }
}
