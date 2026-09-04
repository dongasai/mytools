<?php

namespace Modules\Application\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Application\Models\ApplicationDict;

/**
 * 字典初始数据Seeder
 *
 * 初始化系统字典数据，包含性别、用户状态、启用状态、是否、短信场景等字典类型
 * 使用 upsert 避免重复插入，唯一键为 [dict_type, dict_value]
 */
class DictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // 性别字典
            ['dict_type' => 'gender', 'dict_label' => '男', 'dict_value' => '1', 'dict_sort' => 0, 'status' => 1, 'is_client' => 1, 'remark' => '男性'],
            ['dict_type' => 'gender', 'dict_label' => '女', 'dict_value' => '2', 'dict_sort' => 1, 'status' => 1, 'is_client' => 1, 'remark' => '女性'],

            // 用户状态字典
            ['dict_type' => 'user_status', 'dict_label' => '在职', 'dict_value' => 'on_job', 'dict_sort' => 0, 'status' => 1, 'is_client' => 1, 'remark' => '正常在职'],
            ['dict_type' => 'user_status', 'dict_label' => '待培训', 'dict_value' => 'pending_train', 'dict_sort' => 1, 'status' => 1, 'is_client' => 1, 'remark' => '待培训'],
            ['dict_type' => 'user_status', 'dict_label' => '待上岗', 'dict_value' => 'pending_post', 'dict_sort' => 2, 'status' => 1, 'is_client' => 1, 'remark' => '待上岗'],
            ['dict_type' => 'user_status', 'dict_label' => '已离职', 'dict_value' => 'resigned', 'dict_sort' => 3, 'status' => 1, 'is_client' => 1, 'remark' => '已离职'],
            ['dict_type' => 'user_status', 'dict_label' => '已退休', 'dict_value' => 'retired', 'dict_sort' => 4, 'status' => 1, 'is_client' => 1, 'remark' => '已退休'],

            // 启用状态字典
            ['dict_type' => 'enable_status', 'dict_label' => '启用', 'dict_value' => '1', 'dict_sort' => 0, 'status' => 1, 'is_client' => 1, 'remark' => '通用启用'],
            ['dict_type' => 'enable_status', 'dict_label' => '禁用', 'dict_value' => '0', 'dict_sort' => 1, 'status' => 1, 'is_client' => 1, 'remark' => '通用禁用'],

            // 是否字典
            ['dict_type' => 'yes_no', 'dict_label' => '是', 'dict_value' => '1', 'dict_sort' => 0, 'status' => 1, 'is_client' => 1, 'remark' => '通用是'],
            ['dict_type' => 'yes_no', 'dict_label' => '否', 'dict_value' => '0', 'dict_sort' => 1, 'status' => 1, 'is_client' => 1, 'remark' => '通用否'],

            // 短信场景字典
            ['dict_type' => 'sms_scene', 'dict_label' => '注册', 'dict_value' => '102', 'dict_sort' => 0, 'status' => 1, 'is_client' => 1, 'remark' => '短信注册场景'],
            ['dict_type' => 'sms_scene', 'dict_label' => '找回密码', 'dict_value' => '103', 'dict_sort' => 1, 'status' => 1, 'is_client' => 1, 'remark' => '短信找回密码场景'],

            // 企业能耗分类字典
            ['dict_type' => 'company_energy_classify', 'dict_label' => '电力', 'dict_value' => 'electricity', 'dict_sort' => 0, 'status' => 1, 'is_client' => 1, 'remark' => '电力能源'],
            ['dict_type' => 'company_energy_classify', 'dict_label' => '天然气', 'dict_value' => 'natural_gas', 'dict_sort' => 1, 'status' => 1, 'is_client' => 1, 'remark' => '天然气能源'],
            ['dict_type' => 'company_energy_classify', 'dict_label' => '蒸汽', 'dict_value' => 'steam', 'dict_sort' => 2, 'status' => 1, 'is_client' => 1, 'remark' => '蒸汽能源'],
            ['dict_type' => 'company_energy_classify', 'dict_label' => '其他', 'dict_value' => 'other', 'dict_sort' => 3, 'status' => 1, 'is_client' => 1, 'remark' => '其他能源'],

            // 蒸汽压力字典
            ['dict_type' => 'steam_pres', 'dict_label' => '低压(≤1.0MPa)', 'dict_value' => 'low', 'dict_sort' => 0, 'status' => 1, 'is_client' => 1, 'remark' => '低压蒸汽'],
            ['dict_type' => 'steam_pres', 'dict_label' => '中压(1.0-4.0MPa)', 'dict_value' => 'medium', 'dict_sort' => 1, 'status' => 1, 'is_client' => 1, 'remark' => '中压蒸汽'],
            ['dict_type' => 'steam_pres', 'dict_label' => '高压(>4.0MPa)', 'dict_value' => 'high', 'dict_sort' => 2, 'status' => 1, 'is_client' => 1, 'remark' => '高压蒸汽'],
        ];

        ApplicationDict::upsert(
            $data,
            ['dict_type', 'dict_value'],
            ['dict_label', 'dict_sort', 'status', 'remark']
        );

        $this->command->info('字典初始数据已插入/更新，共 ' . count($data) . ' 条');
    }
}
