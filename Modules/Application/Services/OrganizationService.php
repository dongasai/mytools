<?php

declare(strict_types=1);

namespace Modules\Application\Services;

/**
 * 组织架构服务类
 *
 * 负责组织架构相关的业务逻辑处理
 */
class OrganizationService
{
    /**
     * 构建组织架构树形结构
     *
     * 将平铺的组织架构数据转换为树形结构，对齐 OpenAPI OrganizationTree schema
     *
     * @param  array  $organizations  组织架构数组
     * @return array  树形结构的组织架构数据
     */
    public function buildOrganizationTree(array $organizations): array
    {
        if (empty($organizations)) {
            return [];
        }

        // 将组织架构按父级ID分组
        $groupedByParent = [];
        $rootNodes = [];

        foreach ($organizations as $org) {
            $parentId = $org->parentId ?? '0';
            if (!isset($groupedByParent[$parentId])) {
                $groupedByParent[$parentId] = [];
            }
            $groupedByParent[$parentId][] = $org;

            // 收集根级节点
            if ($org->isRoot()) {
                $rootNodes[] = $org;
            }
        }

        // 递归构建树形结构
        $buildTreeNode = function ($org) use (&$groupedByParent, &$buildTreeNode) {
            $node = [
                'id' => $org->id,  // 对齐 OpenAPI 的 id (string)
                'name' => $org->name,  // 对齐 OpenAPI 的 name
                'description' => $org->description ?? '',  // 对齐 OpenAPI 的 description
            ];

            // 如果有子节点，递归构建 children
            $orgId = $org->id;
            if (isset($groupedByParent[$orgId]) && !empty($groupedByParent[$orgId])) {
                $childNodes = [];
                foreach ($groupedByParent[$orgId] as $child) {
                    $childNodes[] = $buildTreeNode($child);
                }
                $node['children'] = $childNodes;  // 对齐 OpenAPI 的 children
            }

            // 如果没有子节点，设置 children 为 null
            if (!isset($node['children'])) {
                $node['children'] = null;
            }

            return $node;
        };

        // 构建完整的树形结构
        $treeData = [];
        foreach ($rootNodes as $rootOrg) {
            $treeData[] = $buildTreeNode($rootOrg);
        }

        // 如果没有根级节点（所有节点都有父级ID），则将所有节点作为独立节点返回
        if (empty($treeData) && !empty($organizations)) {
            foreach ($organizations as $org) {
                $treeData[] = [
                    'id' => $org->id,
                    'name' => $org->name,
                    'description' => $org->description ?? '',
                    'children' => null,
                ];
            }
        }

        return $treeData;
    }
}