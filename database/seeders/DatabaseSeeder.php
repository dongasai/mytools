<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('开始执行数据库种子数据...');
        $this->command->info('主目录的Seeder 不能有任何Seeder.');

        $this->command->info('🎉 所有种子数据执行完成！');
    }
}
