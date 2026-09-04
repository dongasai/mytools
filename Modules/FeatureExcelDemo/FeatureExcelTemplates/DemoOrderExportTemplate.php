<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\FeatureExcelTemplates;

use Modules\FeatureExcel\Templates\Base\AbstractExportTemplate;
use Modules\FeatureExcel\Engines\Template\FieldMapping;

/**
 * 演示订单导出模板
 */
class DemoOrderExportTemplate extends AbstractExportTemplate
{
    protected string $name = '演示订单导出模板';
    protected string $sheetName = '订单数据';
    protected array $headers = ['订单号', '客户姓名', '产品名称', '数量', '单价', '总金额', '订单日期', '状态', '备注'];
    protected string $fileNamePattern = 'orders_{date}';

    protected function defineFields(): array
    {
        return [
            'order_no' => FieldMapping::make('A', 'string')->name('订单号'),
            'customer_name' => FieldMapping::make('B', 'string')->name('客户姓名'),
            'product_name' => FieldMapping::make('C', 'string')->name('产品名称'),
            'quantity' => FieldMapping::make('D', 'integer')->name('数量'),
            'unit_price' => FieldMapping::make('E', 'float')->name('单价')->format('number:2'),
            'total_amount' => FieldMapping::make('F', 'float')->name('总金额')->format('number:2'),
            'order_date' => FieldMapping::make('G', 'date')->name('订单日期')->format('date:Y-m-d'),
            'status' => FieldMapping::make('H', 'string')->name('状态'),
            'remark' => FieldMapping::make('I', 'string')->name('备注'),
        ];
    }

    public function formatRow(array $row): array
    {
        if (isset($row['status'])) {
            $map = ['pending' => '待处理', 'confirmed' => '已确认', 'shipped' => '已发货', 'completed' => '已完成'];
            $row['status'] = $map[$row['status']] ?? $row['status'];
        }
        return $row;
    }
}