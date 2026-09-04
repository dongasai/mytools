<?php

namespace Modules\Application\DcatAdmin\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Application\Models\ApplicationConfig;
use Modules\Application\Services\ConfigService;

trait ConfigEditForm
{
    public function run($input)
    {
        $id = $this->payload['id'] ?? null;
        if (! $id) {
            return $this->error('input-error');
        }
        /**
         * @var ApplicationConfig $model
         */
        $model = ApplicationConfig::query()->find($id);
        //        dump($model);
        if (! $model) {
            return $this->error('noinfo');
        }
        DB::beginTransaction();

        $model->value = $input['value'];
        if (! $model->save()) {
            return $this->error('error');
        }
        DB::commit();
        ConfigService::clear_cache();

        return $this->success('ok')->refresh();
    }
}
