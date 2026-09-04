<?php

namespace Modules\FeatureAi\Database\Seeders;

use Illuminate\Database\Seeder;

class FeatureAiDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AiProviderSeeder::class,
        ]);
    }
}