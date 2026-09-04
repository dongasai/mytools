<?php

namespace Modules\DcatAdmin\DcatAdmin\Repository;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Eloquent9999999Repository extends \Dcat\Admin\Repositories\EloquentRepository
{
    public function getEloquentClass()
    {
        return $this->eloquentClass;
    }

    /**
     *  更新后
     *
     * @var array
     */
    public $update_after;

    /**
     * 更新前
     *
     * @var array
     */
    public $update_before;

    /**
     * 新增, 生成数据
     *
     * @var array
     */
    public $add_after;

    /**
     * 新增 传入数据
     *
     * @var array
     */
    public $add_before;

    /**
     * 新增记录.
     *
     * @return mixed
     */
    public function store(Form $form)
    {
        $result = null;

        DB::transaction(function () use ($form, &$result) {
            $model = $this->model();

            $updates = $form->updates();

            [$relations, $relationKeyMap] = $this->getRelationInputs($model, $updates);

            if ($relations) {
                $updates = Arr::except($updates, array_keys($relationKeyMap));
            }
            $this->add_before = $updates;

            foreach ($updates as $column => $value) {
                $model->setAttribute($column, $value);
            }

            $result = $model->save();
            $this->add_after = $model->toArray();

            $this->updateRelation($form, $model, $relations, $relationKeyMap);
        });

        return $this->model()->getKey();
    }

    /**
     * 更新数据.
     *
     * @return bool
     */
    public function update(Form $form)
    {
        /* @var EloquentModel $builder */
        $model = $this->model();

        if (! $model->getKey()) {
            $model->exists = true;

            $model->setAttribute($model->getKeyName(), $form->getKey());
        }

        $result = null;

        DB::transaction(function () use ($form, $model, &$result) {
            $updates = $form->updates();

            [$relations, $relationKeyMap] = $this->getRelationInputs($model, $updates);

            if ($relations) {
                $updates = Arr::except($updates, array_keys($relationKeyMap));
            }

            foreach ($updates as $column => $value) {
                $this->update_before[$column] = $model->getAttribute($column);
                /* @var EloquentModel $model */
                $model->setAttribute($column, $value);
                $this->update_after[$column] = $model->getAttribute($column);
            }

            $result = $model->update();

            $this->updateRelation($form, $model, $relations, $relationKeyMap);
        });

        return $result;
    }

    public function get(Grid\Model $model)
    {

        $this->setSort($model);
        $ps = $this->resolvePerPage($model, null);

        $model->forPage($ps[3], $ps[0])->addQuery('get', [$this->getGridColumns()]);
        // dd($model);
        $query = $this->newQuery();

        if ($this->relations) {
            $query->with($this->relations);
        }

        $total = 999999999;
        $data = $model->apply($query, true, $this->getGridColumns());
        //        dd($ps,request()->path());
        $paginator = new LengthAwarePaginator(
            $data,
            $total,
            $ps[0], // 传入每页显示行数
            $ps[3], // 传入当前页码
            [
                'path' => '/'.request()->path(),
            ]
        );

        return $paginator;
    }
}
