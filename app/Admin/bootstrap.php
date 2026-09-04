<?php

use App\Admin\ModuleHelper;
use Dcat\Admin\Admin;

/**
 * Dcat-admin - admin builder based on Laravel.
 *
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 */
Admin::css("/admin.css");

// 菜单加载逻辑已改造为数据库持久化模式
// 移除每次请求的动态加载，改用手动同步机制
// ModuleHelper::loadModuleAdminMenus("admin_menu"); // 旧代码已废弃

// 首次部署时检测同步页面菜单是否存在，不存在则自动同步
$syncMenuExists = \Modules\DcatAdmin\Models\AdminMenu::where('uri', 'module_dcatadmin/menu-sync')->exists();
if (!$syncMenuExists) {
    // 同步页面菜单不存在，执行自动同步
    try {
        app(\Modules\DcatAdmin\Services\MenuSyncService::class)->syncAll();
        \Illuminate\Support\Facades\Log::info('首次部署：菜单自动同步完成');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('首次部署：菜单自动同步失败', [
            'error' => $e->getMessage()
        ]);
    }
}

//  10*** DcatAdmin 模块
//  11*** User 模块
//  12*** Demo5模块
//  13*** Base模块
//  14*** Account 模块 账户
//  15*** Application 模块 应用
//  16 xxx Friend 模块菜单ID
//  17*** Merchant 模块
//  19*** ContentVersion 模块 内容版本管理
//  20*** Point 模块 积分模块菜单ID
//  22*** Workflow 模块 核心-审核流模块
//  28*** Diyform 模块 核心- 自定义表单 模块


# 学校

//  100*** SchoolTeacher 模块 学校-老师
//  101*** SchoolOrg 模块 学校-组织架构
// 以后都用 1****

# 商城

//  200*** ShopGood 模块 商城-商品
//  201*** ShopOrder 模块 商城-订单模块
//  202*** Merchant 模块
// 以后都用 2** ***

# 医院
// 300 *** Hospital 医院管理后台
// 301*** Attendance 考勤
// 302*** Regulation 制度模块
// 303*** exam 考试模块
// 304*** learn 学习模块 
// 以后都用 3** ***

# 游戏
//  400*** GameTask 模块 游戏-任务 [已实现]
//  401*** GameShop 模块 游戏-商店 [已实现]
//  402*** GamePet 模块 游戏-宠物
//  403*** GameBase 模块
// 以后都用 4** **

# 功能模块,Modules目录
// 500*** FeatureSms 短信模块 
// 以后都用 5** **

# 支付
//  600xxx Pay -Payadmin 模块 支付后台的菜单ID
// 以后都用 6xx xxx

# 其他
//  700xxx Taska -Taskaadmin 模块 Taska模块的后台
// 以后都用 7xx xxx

// 999*** 最大号码
