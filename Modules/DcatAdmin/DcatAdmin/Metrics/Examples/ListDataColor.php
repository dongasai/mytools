<?php

namespace Modules\DcatAdmin\DcatAdmin\Metrics\Examples;

use Illuminate\Http\Request;

/**
 * 系统报警
 * Class Alarm
 */
class ListDataColor extends \Modules\DcatAdmin\DcatAdmin\Metrics\Examples\Round
{
    protected $height = 150;

    /**
     * 内容宽度.
     *
     * @var array
     */
    protected $contentWidth = [12, 0];

    const TYPE_ERROR = 'text-danger';

    const TYPE_WARNING = 'text-warning';

    const TYPE_OK = 'text-primary';

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();
        $this->chart = null;
        $this->title('系统状态监控');
    }

    /**
     * 获取硬编码演示数据
     *
     * @return array
     */
    protected function getData()
    {
        return [
            ['title' => '系统错误', 'value' => 12, 'type' => self::TYPE_ERROR],
            ['title' => '警告信息', 'value' => 8, 'type' => self::TYPE_WARNING],
            ['title' => '正常运行', 'value' => 156, 'type' => self::TYPE_OK],
        ];
    }

    /**
     * 处理请求
     *
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $data = $this->getData();
        // 卡片内容
        $this->withContent($data);
    }

    /**
     * 卡片内容.
     *
     * @param  int  $finished
     * @param  int  $pending
     * @param  int  $rejected
     * @return $this
     */
    public function withContent($data)
    {
        $string = '';
        foreach ($data as $key => $value) {

            $string .= <<<HTML
<div class="chart-info d-flex justify-content-between mb-1 {$value['type']}" >
  <div class="series-info d-flex align-items-center">
      <i class="fa fa-circle-o text-bold-700 "></i>
      <span class="text-bold-600 ml-50">{$value['title']}</span>
  </div>
  <div class="product-result">
      <span>{$value['value']}</span>
  </div>
</div>
HTML;
        }

        return $this->content(
            <<<HTML
<div class="col-12 d-flex flex-column flex-wrap text-center" >
    $string
</div>
HTML
        );
    }
}
