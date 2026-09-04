<?php

namespace Modules\FeatureExcelDemo\Database\Seeders;

use Illuminate\Database\Seeder;

class FeatureExcelDemoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DemoOrderSeeder::class,
        ]);
    }
}
