<?php

namespace Modules\AClean\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AClean\Models\CleanupConfig;

/**
 * Cleanup 模块数据库填充器
 */
class CleanupDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CleanupConfigSeeder::class,
            CleanupPlanSeeder::class,
        ]);
    }
}