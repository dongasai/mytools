<?php

namespace Modules\Application\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;

/**
 * PHP禁用函数检查卡片
 *
 * 检查常用可能被禁用的PHP函数，帮助发现服务器环境问题
 */
class PhpDisabledFunctionsMetric extends Card
{
    /**
     * 初始化卡片
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->title('PHP 禁用函数检查');
        $this->height(300);  // 大型卡片：检查项较多
    }

    /**
     * 处理异步请求
     *
     * @param Request $request
     * @return void
     */
    public function handle(Request $request)
    {
        $result = $this->checkFunctions();
        $this->withContent($result);
    }

    /**
     * 检查常用PHP函数是否可用
     *
     * @return array 检查结果
     */
    protected function checkFunctions(): array
    {
        // 获取被禁用的函数列表
        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        $disabled = array_filter($disabled);

        // 检查的关键函数（按功能分组）
        $checks = [
            '文件操作' => [
                'symlink' => '创建符号链接',
                'link' => '创建硬链接',
                'readlink' => '读取符号链接',
                'fopen' => '打开文件',
                'file_get_contents' => '读取文件内容',
                'file_put_contents' => '写入文件内容',
                'mkdir' => '创建目录',
                'rmdir' => '删除目录',
            ],
            '命令执行' => [
                'exec' => '执行外部程序',
            ],
            '进程控制' => [
                'proc_open' => '打开进程',
                'proc_close' => '关闭进程',
            ],
        ];

        $results = [];
        $totalChecked = 0;
        $totalDisabled = 0;

        foreach ($checks as $category => $functions) {
            $categoryResults = [];
            $disabledCount = 0;

            foreach ($functions as $func => $desc) {
                $totalChecked++;
                $isDisabled = in_array($func, $disabled);
                if ($isDisabled) {
                    $totalDisabled++;
                    $disabledCount++;
                }
                $categoryResults[] = [
                    'function' => $func,
                    'description' => $desc,
                    'disabled' => $isDisabled,
                ];
            }

            $results[$category] = [
                'items' => $categoryResults,
                'disabled_count' => $disabledCount,
                'total' => count($functions),
            ];
        }

        return [
            'categories' => $results,
            'total_checked' => $totalChecked,
            'total_disabled' => $totalDisabled,
            'status' => $totalDisabled > 0 ? 'warning' : 'ok',
        ];
    }

    /**
     * 设置卡片内容
     *
     * @param array $data 检查结果数据
     * @return $this
     */
    public function withContent(array $data)
    {
        $status = $data['status'];
        $totalChecked = $data['total_checked'];
        $totalDisabled = $data['total_disabled'];
        $categories = $data['categories'];

        // 状态图标和颜色
        if ($totalDisabled === 0) {
            $statusIcon = '<i class="fa fa-check-circle text-success" style="font-size: 1.5rem;"></i>';
            $statusText = '所有关键函数可用';
            $statusClass = 'text-success';
        } else {
            $statusIcon = '<i class="fa fa-exclamation-triangle text-warning" style="font-size: 1.5rem;"></i>';
            $statusText = "发现 {$totalDisabled} 个函数被禁用";
            $statusClass = 'text-warning';
        }

        // 构建列表HTML
        $listHtml = '';
        foreach ($categories as $category => $categoryData) {
            if ($categoryData['disabled_count'] > 0) {
                $listHtml .= "<div class='mb-2'>";
                $listHtml .= "<div class='text-muted small mb-1'>{$category}</div>";

                foreach ($categoryData['items'] as $item) {
                    if ($item['disabled']) {
                        $func = e($item['function']);
                        $desc = e($item['description']);
                        $listHtml .= "<div class='d-flex justify-content-between py-1 px-2 small'>";
                        $listHtml .= "<span class='text-danger'><i class='fa fa-times-circle'></i> {$func}</span>";
                        $listHtml .= "<span class='text-muted'>{$desc}</span>";
                        $listHtml .= "</div>";
                    }
                }

                $listHtml .= "</div>";
            }
        }

        if (empty($listHtml)) {
            $listHtml = '<div class="text-center py-3"><div class="text-success mb-2"><i class="fa fa-check-circle fa-2x"></i></div><p class="text-muted mb-0">所有关键函数均可正常使用</p></div>';
        }

        $html = <<<HTML
<div class="d-flex flex-column" style="padding: 0.5rem; line-height: 1.4;">
    <div class="d-flex align-items-center mb-3">
        <div class="mr-2">{$statusIcon}</div>
        <div>
            <div class="{$statusClass} font-weight-bold">{$statusText}</div>
            <div class="text-muted small">已检查 {$totalChecked} 个关键函数</div>
        </div>
    </div>
    <div style="max-height: 180px; overflow-y: auto;">
        {$listHtml}
    </div>
</div>
HTML;

        return $this->content($html);
    }
}