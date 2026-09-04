<?php

namespace Modules\Application\DcatAdmin\Helper;

use Dcat\Admin\Grid\Filter;
use Modules\Application\Enums\CONFIG_TYPE;
use Modules\Application\Enums\VIEW_TYPE;

/**
 * 筛选器辅助特性
 *
 * 提供系统模块后台控制器的筛选器构建功能的具体实现
 * 注意：不覆盖基类方法，只添加业务特定的筛选器方法
 */
trait FilterHelperTrait
{
    /**
     * 添加配置键名筛选
     */
    public function likeKeyname()
    {
        return $this->filter->like('keyname', '键名');
    }

    /**
     * 添加配置标题筛选
     */
    public function likeTitle()
    {
        return $this->filter->like('title', '标题');
    }

    /**
     * 添加配置类型筛选
     */
    public function equalConfigType()
    {
        return $this->filter->equal('type', '类型')->select([
            CONFIG_TYPE::TYPE_INT->value => '整数',
            CONFIG_TYPE::TYPE_IMG->value => '图片',
            CONFIG_TYPE::TYPE_BOOL->value => '布尔值',
            CONFIG_TYPE::TYPE_STRING->value => '字符串',
            CONFIG_TYPE::TYPE_FLOAT->value => '浮点数',
            CONFIG_TYPE::TYPE_FILE->value => '文件',
            CONFIG_TYPE::TYPE_PERCENTAGE->value => '百分比',
            CONFIG_TYPE::TYPE_TIME->value => '时间',
            CONFIG_TYPE::TYPE_IS->value => '是否',
            CONFIG_TYPE::TYPE_JSON->value => 'JSON数组',
            CONFIG_TYPE::TYPE_EMBEDS->value => 'JSON键值对',
        ]);
    }

    /**
     * 添加配置值筛选
     */
    public function likeValue()
    {
        return $this->filter->like('value', '值');
    }

    /**
     * 添加配置分组筛选
     */
    public function equalGroup()
    {
        return $this->filter->equal('group', '分组');
    }

    /**
     * 添加配置子分组筛选
     */
    public function equalGroup2()
    {
        return $this->filter->equal('group2', '子分组');
    }

    /**
     * 添加配置描述筛选
     */
    public function likeDesc()
    {
        return $this->filter->like('desc', '描述');
    }

    /**
     * 添加是否客户端可用筛选
     */
    public function equalIsClient()
    {
        return $this->filter->equal('is_client', '客户端可用')->select([
            0 => '否',
            1 => '是',
        ]);
    }

    /**
     * 添加视图类型筛选
     */
    public function equalViewType()
    {
        return $this->filter->equal('type1', '视图类型')->select([
            VIEW_TYPE::PRIVATE->value => '私有',
            VIEW_TYPE::PUBLIC->value => '公共',
        ]);
    }

    /**
     * 添加路由名称筛选
     */
    public function likeRouterName()
    {
        return $this->filter->like('router_name', '路由名称');
    }

    /**
     * 添加管理员ID筛选
     */
    public function equalAdminId()
    {
        return $this->filter->equal('admin_id', '管理员ID');
    }

    /**
     * 添加创建时间筛选
     */
    public function betweenCreatedAt()
    {
        return $this->filter->between('created_at', '创建时间')->datetime();
    }

    /**
     * 添加更新时间筛选
     */
    public function betweenUpdatedAt()
    {
        return $this->filter->between('updated_at', '更新时间')->datetime();
    }
}