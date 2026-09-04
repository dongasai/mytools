<?php

declare(strict_types=1);

namespace Modules\FeatureExcelDemo\FeatureExcelTemplates;

use Modules\FeatureExcel\Engines\Template\FieldMapping;
use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;

/**
 * 演示订单导入模板
 */
class DemoOrderImportTemplate extends AbstractImportTemplate
{
    protected string $name = '演示订单导入模板';

    /** @var int 数据从第3行开始（第2行为示例数据，导入时跳过） */
    protected int $startRow = 3;

    /**
     * 当前处理的Excel行号（由validateRow设置，供transformRow使用）
     */
    private ?int $currentRowNumber = null;

    protected function defineFields(): array
    {
        return [
            'order_no' => FieldMapping::make('A', 'string')->name('订单号')->required(),
            'customer_name' => FieldMapping::make('B', 'string')->name('客户姓名')->required(),
            'product_name' => FieldMapping::make('C', 'string')->name('产品名称')->required(),
            'quantity' => FieldMapping::make('D', 'integer')->name('数量')->required(),
            'unit_price' => FieldMapping::make('E', 'float')->name('单价')->required(),
            'order_date' => FieldMapping::make('G', 'date')->name('订单日期')->required(),
            'status' => FieldMapping::make('H', 'string')->name('状态')->setDefault('pending'),
            'remark' => FieldMapping::make('I', 'string')->name('备注')->nullable(),
        ];
    }

    /**
     * 行级业务验证钩子
     *
     * 保存当前行号供transformRow使用，并执行业务级验证：
     * - 订单号格式验证（必须为大写2位字母+6位数字，如 OR000001）
     * - 数量合理性验证（必须大于等于1）
     * - 单价合理性验证（必须大于0）
     *
     * @param  array  $row  单行数据
     * @param  int  $rowNumber  Excel实际行号
     * @return string|null 失败原因（null表示通过）
     */
    public function validateRow(array $row, int $rowNumber): ?string
    {
        $this->currentRowNumber = $rowNumber;

        // 订单号格式验证：2位大写字母 + 6位数字
        if (isset($row['order_no']) && ! preg_match('/^[A-Z]{2}\d{6}$/', (string) $row['order_no'])) {
            return "第{$rowNumber}行：订单号格式错误，必须是2位大写字母+6位数字（如 OR000001）";
        }

        // 数量合理性验证：必须大于等于1
        if (isset($row['quantity']) && (int) $row['quantity'] < 1) {
            return "第{$rowNumber}行：数量必须大于等于1";
        }

        // 单价合理性验证：必须大于0
        if (isset($row['unit_price']) && (float) $row['unit_price'] <= 0) {
            return "第{$rowNumber}行：单价必须大于0";
        }

        return null;
    }

    /**
     * 行级业务转换钩子
     *
     * 自动计算总金额，将中文状态映射为英文枚举值，并附加原始Excel行号
     *
     * @param  array  $row  单行数据
     * @return array 转换后的数据行
     */
    public function transformRow(array $row): array
    {
        // 自动计算总金额
        if (isset($row['quantity']) && isset($row['unit_price'])) {
            $row['total_amount'] = $row['quantity'] * $row['unit_price'];
        }

        // 状态中文→英文反向映射（与导出formatRow对应，确保导出文件可直接导入）
        if (isset($row['status']) && is_string($row['status'])) {
            $statusMap = [
                '待处理' => 'pending',
                '已确认' => 'confirmed',
                '已发货' => 'shipped',
                '已完成' => 'completed',
            ];
            // 如果输入是中文，映射为英文；否则保持原值（支持直接输入英文）
            $row['status'] = $statusMap[$row['status']] ?? $row['status'];
        }

        // 附加原始Excel行号，用于后续错误提示
        $row['_excel_row_number'] = $this->currentRowNumber;

        return $row;
    }
}
