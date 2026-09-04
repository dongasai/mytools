<?php

namespace Modules\Application\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Application\Models\ApplicationDict;

/**
 * 蒸汽压力字典数据填充
 *
 * 填充 steam_pressure 字典类型，包含71个压力值（0.001-30.000 MPa）
 */
class SteamPressureDictSeeder extends Seeder
{
    /**
     * 压力值列表（单位：MPa）
     *
     * @var array
     */
    private array $pressureValues = [
        '0.001', '0.002', '0.003', '0.004', '0.005', '0.006', '0.007', '0.008', '0.009', '0.010',
        '0.015', '0.020', '0.025', '0.030', '0.040', '0.050', '0.060', '0.070', '0.080', '0.090',
        '0.100', '0.120', '0.140', '0.160', '0.180', '0.200', '0.250', '0.300', '0.350', '0.400',
        '0.450', '0.500', '0.600', '0.700', '0.800', '0.900', '1.000', '1.100', '1.200', '1.300',
        '1.400', '1.500', '1.600', '1.900', '2.000', '2.200', '2.400', '2.600', '2.800', '3.000',
        '3.500', '4.000', '5.000', '6.000', '7.000', '8.000', '9.000', '10.000', '11.000', '12.000',
        '13.000', '14.000', '15.000', '16.000', '17.000', '18.000', '19.000', '20.000', '21.000',
        '22.000', '25.000', '30.000',
    ];

    /**
     * 运行数据库填充
     *
     * @return void
     */
    public function run(): void
    {
        $data = [];
        $sort = 0;

        foreach ($this->pressureValues as $value) {
            $data[] = [
                'dict_type'  => 'steam_pressure',
                'dict_label' => $value . ' MPa',
                'dict_value' => $value,
                'dict_sort'  => $sort++,
                'status'     => 1,
                'is_client'  => 1,
                'remark'     => '蒸汽压力',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 批量插入或更新（支持重复执行）
        ApplicationDict::upsert($data, ['dict_type', 'dict_value'], [
            'dict_label',
            'dict_sort',
            'status',
            'is_client',
            'remark',
            'updated_at',
        ]);

        $this->command->info('蒸汽压力字典数据填充完成，共 ' . count($data) . ' 条记录');
    }
}