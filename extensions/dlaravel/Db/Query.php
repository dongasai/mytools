<?php

namespace DLaravel\Db;

/**
 * 查询构建特性
 *
 * 提供常用的查询条件构建方法
 */
trait Query
{
    /**
     * 如果是数字,则进行where条件查询
     *
     * @param  string  $field  字段名
     * @param  string  $op  操作符
     * @param  string  $fieldSql  自定义SQL字段名
     * @return $this
     */
    public function queryNumber($field, $op = '=', $fieldSql = '')
    {
        if (! $fieldSql) {
            $fieldSql = $field;
        }
        if ($this->isNumber($field)) {
            $this->query = $this->query->where($fieldSql, $op, $this->getNumber($field));
        }

        return $this;
    }

    public function queryString($field, $op = '=')
    {
        if ($this->isString($field)) {
            $this->query = $this->query->where($field, $op, $this->getNumber($field));
        }

        return $this;
    }

    /**
     * 存在,且notEmpay
     *
     * @return $this
     */
    public function queryExist($field, $op = '=')
    {
        if ($this->notNull($field) && $this->notEmpty($field)) {
            $this->query = $this->query->where($field, $op, $this->data[$field]);
        }

        return $this;

    }
}
