<?php

namespace DLaravel\Commands;

use Illuminate\Console\Command;

/**
 * 生成打包信息
 *
 * php artisan  docker:buildinfo
 */
class BuildInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:buildinfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成打包信息';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $config_build = base_path('config/build.php');
        $systemInfo = php_uname();
        $SERVER_SOFTWARE = $_SERVER['SERVER_SOFTWARE'] ?? '';
        $envos = getenv('OS');
        $unamea = exec('uname -a');

        $build = [
            'time' => time(),
            'systemInfo' => $systemInfo,
            'SERVER_SOFTWARE' => $SERVER_SOFTWARE,
            'envos' => $envos,
            'unamea' => $unamea,
        ];
        $string = var_export($build, true);
        file_put_contents($config_build, "<?php \r\n return $string ;");

        return Command::SUCCESS;
    }
}
