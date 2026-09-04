<?php

namespace Modules\FeatureExcel\Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Mockery;
use Modules\FeatureExcel\Engines\Template\FieldMapping;
use Modules\FeatureExcel\Logics\HookApplyLogic;
use Modules\FeatureExcel\Services\ModuleFeatureExcelService;
use Modules\FeatureExcel\Templates\Base\AbstractExportTemplate;
use Modules\FeatureExcel\Templates\Base\AbstractImportTemplate;
use PHPUnit\Framework\TestCase;

/**
 * 模板基类与钩子应用单元测试
 *
 * 覆盖：FieldMapping 流式接口、抽象基类实例化与字段注入、
 * HookApplyLogic 钩子应用、Service 全流程（手动创建 Laravel 容器）
 */
class TemplateBaseTest extends TestCase
{
    /**
     * 测试用存储目录
     */
    private string $storageDir;

    /**
     * 测试用临时 CSV 文件
     */
    private string $csvOk;

    /**
     * 测试用临时 CSV 文件（含业务验证失败行）
     */
    private string $csvFail;

    /**
     * 测试用临时 CSV 文件（含引擎字段验证失败行）
     */
    private string $csvEngineFail;

    /**
     * 创建测试环境：Laravel 容器（storage_path 支持）+ 测试磁盘 + 临时 CSV 文件
     *
     * 引擎路径解析只接受相对路径（相对 AFile 临时盘根目录），
     * 故注册 test_tmp 磁盘并 mock 数据库临时盘配置查询（StorageConfigService::getTempDisk），
     * 绕过数据库与缓存。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->storageDir = sys_get_temp_dir().'/fexcel_test_'.uniqid();
        // 测试磁盘根目录（模拟 AFile 临时盘 root）
        $diskRoot = $this->storageDir.'/disk';
        if (! is_dir($diskRoot)) {
            mkdir($diskRoot, 0777, true);
        }

        $app = new Application(dirname(__DIR__, 4));
        $app->useStoragePath($this->storageDir);
        // 注册测试磁盘配置（手动容器无 config 绑定，需显式注入）
        $app->instance('config', new \Illuminate\Config\Repository([
            'filesystems' => [
                'disks' => [
                    'test_tmp' => [
                        'driver' => 'local',
                        'root' => $diskRoot,
                        'throw' => false,
                    ],
                ],
            ],
        ]));
        // 手动容器无 FilesystemServiceProvider，需显式绑定 filesystem 与 Facade 应用实例
        $app->singleton('filesystem', fn ($app): \Illuminate\Filesystem\FilesystemManager => new \Illuminate\Filesystem\FilesystemManager($app));
        \Illuminate\Support\Facades\Facade::setFacadeApplication($app);
        \Illuminate\Support\Facades\Facade::clearResolvedInstances();
        // mock 数据库临时盘配置查询，返回 test_tmp 磁盘
        $storageConfigMock = Mockery::mock('alias:Modules\AFile\Services\StorageConfigService');
        $storageConfigMock->shouldReceive('getTempDisk')->andReturn((object) ['name' => 'test_tmp']);
        Container::setInstance($app);

        // 相对路径（相对临时盘根目录），file_path 只接受相对路径
        $this->csvOk = 'import_ok.csv';
        $this->csvFail = 'import_fail.csv';
        $this->csvEngineFail = 'import_engine_fail.csv';

        file_put_contents($diskRoot.'/import_ok.csv', "表计ID,能耗值\n5,100\n3,300\n");
        file_put_contents($diskRoot.'/import_fail.csv', "表计ID,能耗值\n5,100\n0,200\n3,300\n");
        file_put_contents($diskRoot.'/import_engine_fail.csv', "表计ID,能耗值\n5,abc\n3,300\n");
    }

    /**
     * 清理测试环境
     */
    protected function tearDown(): void
    {
        Mockery::close();

        if (is_dir($this->storageDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->storageDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileInfo) {
                $fileInfo->isDir() ? rmdir($fileInfo->getRealPath()) : unlink($fileInfo->getRealPath());
            }
            rmdir($this->storageDir);
        }

        Container::setInstance(null);
        parent::tearDown();
    }

    /**
     * 创建测试导入模板（流式接口 + 业务钩子）
     */
    private function createImportTemplate(): AbstractImportTemplate
    {
        return new class extends AbstractImportTemplate
        {
            protected string $name = '测试导入模板';

            protected string $format = 'csv';

            protected function defineFields(): array
            {
                return [
                    'meter_id' => FieldMapping::make('A', 'integer')->name('表计ID')->required(),
                    'energy' => FieldMapping::make('B', 'float')->name('能耗值')->required(),
                ];
            }

            public function validateRow(array $row, int $rowNumber): ?string
            {
                return (int) $row['meter_id'] > 0 ? null : '表计ID必须大于0';
            }

            public function transformRow(array $row): array
            {
                $row['energy'] = (float) $row['energy'] * 2;

                return $row;
            }
        };
    }

    /**
     * 创建测试导出模板
     */
    private function createExportTemplate(): AbstractExportTemplate
    {
        return new class extends AbstractExportTemplate
        {
            protected string $name = '测试导出模板';

            protected string $format = 'csv';

            protected function defineFields(): array
            {
                return [
                    'meter_id' => FieldMapping::make('A', 'integer')->name('表计ID'),
                    'energy' => FieldMapping::make('B', 'float')->name('能耗值'),
                ];
            }

            public function formatRow(array $row): array
            {
                $row['energy'] = round((float) $row['energy'], 1);

                return $row;
            }
        };
    }

    /**
     * 测试 FieldMapping 流式接口完整链式调用
     */
    public function test_field_mapping_fluent_interface(): void
    {
        $mapping = FieldMapping::make('A', 'integer')
            ->name('表计ID')
            ->required()
            ->validation(['min:1'])
            ->transform('int')
            ->format('number:2')
            ->nullable()
            ->defaultValue(0)
            ->timezone('Asia/Shanghai', 'UTC')
            ->type('float');

        $this->assertSame('A', $mapping->getColumn());
        $this->assertSame('float', $mapping->getType());
        $this->assertSame('表计ID', $mapping->getName());
        $this->assertTrue($mapping->isRequired());
        $this->assertSame(['min:1'], $mapping->getValidation());
        $this->assertSame('int', $mapping->getTransform());
        $this->assertSame('number:2', $mapping->getFormat());
        $this->assertTrue($mapping->isNullable());
        $this->assertSame(0, $mapping->getDefault());
        $this->assertSame('Asia/Shanghai', $mapping->getTimezoneFrom());
        $this->assertSame('UTC', $mapping->getTimezoneTo());
    }

    /**
     * 测试 FieldMapping 流式接口与 fromArray 兼容并存
     */
    public function test_field_mapping_from_array_compatible(): void
    {
        $mapping = FieldMapping::fromArray(['column' => 'A', 'name' => 'ID', 'type' => 'integer']);

        $this->assertSame('A', $mapping->getColumn());
        $this->assertSame('ID', $mapping->getName());
        $this->assertSame('integer', $mapping->getType());
        $this->assertFalse($mapping->isRequired());
    }

    /**
     * 测试抽象导入模板：属性覆盖 + 字段注入
     */
    public function test_abstract_import_template_instance(): void
    {
        $template = $this->createImportTemplate();

        $this->assertSame('测试导入模板', $template->getName());
        $this->assertSame('csv', $template->getFormat());
        $this->assertSame(2, $template->getStartRow());
        $this->assertCount(2, $template->getFields());
        $this->assertArrayHasKey('meter_id', $template->getFields());
    }

    /**
     * 测试抽象导出模板：属性覆盖 + 字段注入
     */
    public function test_abstract_export_template_instance(): void
    {
        $template = $this->createExportTemplate();

        $this->assertSame('测试导出模板', $template->getName());
        $this->assertSame('csv', $template->getFormat());
        $this->assertCount(2, $template->getFields());
    }

    /**
     * 测试 HookApplyLogic：业务验证失败收集错误（含行号）
     */
    public function test_hook_apply_import_validation_fail(): void
    {
        $template = $this->createImportTemplate();
        $result = HookApplyLogic::applyImportHooks(
            [
                ['meter_id' => 5, 'energy' => 100],
                ['meter_id' => 0, 'energy' => 200],
                ['meter_id' => 3, 'energy' => 300],
            ],
            $template
        );

        $this->assertFalse($result->isSuccess());
        $errors = $result->getErrors();
        // 行索引 1 失败（startRow=2 → Excel 第3行）
        $this->assertArrayHasKey(1, $errors);
        $this->assertSame('第3行 业务验证: 表计ID必须大于0', $errors[1][0]);
    }

    /**
     * 测试 HookApplyLogic：全部通过 + transformRow 生效
     */
    public function test_hook_apply_import_transform(): void
    {
        $template = $this->createImportTemplate();
        $result = HookApplyLogic::applyImportHooks(
            [
                ['meter_id' => 5, 'energy' => 100],
                ['meter_id' => 3, 'energy' => 300],
            ],
            $template
        );

        $this->assertTrue($result->isSuccess());
        $this->assertSame(
            [
                ['meter_id' => 5, 'energy' => 200.0],
                ['meter_id' => 3, 'energy' => 600.0],
            ],
            $result->getData()
        );
    }

    /**
     * 测试 HookApplyLogic：导出 formatRow 生效
     */
    public function test_hook_apply_export_format(): void
    {
        $template = $this->createExportTemplate();
        $formatted = HookApplyLogic::applyExportHooks(
            [
                ['meter_id' => 1, 'energy' => 1.234],
                ['meter_id' => 2, 'energy' => 2.345],
            ],
            $template
        );

        $this->assertSame(
            [
                ['meter_id' => 1, 'energy' => 1.2],
                ['meter_id' => 2, 'energy' => 2.3],
            ],
            $formatted
        );
    }

    /**
     * 测试 Service 全流程：业务验证失败（引擎通过、钩子拒绝）
     */
    public function test_service_import_business_validation_fail(): void
    {
        $template = $this->createImportTemplate();
        $result = ModuleFeatureExcelService::importWithValidation($this->csvFail, $template);

        $this->assertFalse($result->isSuccess());
        $this->assertArrayHasKey(1, $result->getErrors());
        $this->assertSame('第3行 业务验证: 表计ID必须大于0', $result->getErrors()[1][0]);
    }

    /**
     * 测试 Service 全流程：引擎字段验证失败（错误原样透传）
     */
    public function test_service_import_engine_validation_fail(): void
    {
        $template = $this->createImportTemplate();
        $result = ModuleFeatureExcelService::importWithValidation($this->csvEngineFail, $template);

        $this->assertFalse($result->isSuccess());
        // 第一行数据（索引0，Excel 第2行）energy=abc 字段验证失败
        $this->assertArrayHasKey(0, $result->getErrors());
        $this->assertSame('第2行 能耗值: 能耗值必须是数字', $result->getErrors()[0]['energy']);
    }

    /**
     * 测试 Service 全流程：全部通过 + 引擎类型转换 + 钩子转换
     */
    public function test_service_import_success(): void
    {
        $template = $this->createImportTemplate();
        $result = ModuleFeatureExcelService::importWithValidation($this->csvOk, $template);

        $this->assertTrue($result->isSuccess());
        $data = $result->getData();
        $this->assertCount(2, $data);
        $this->assertSame(5, $data[0]['meter_id']);
        $this->assertSame(200.0, $data[0]['energy']);
        $this->assertSame(600.0, $data[1]['energy']);
    }

    /**
     * 测试 Service 不验证导入模式（仅业务转换）
     */
    public function test_service_import_without_validation(): void
    {
        $template = $this->createImportTemplate();
        $data = ModuleFeatureExcelService::import($this->csvFail, $template);

        $this->assertCount(3, $data);
        // 含 0 的行也通过（跳过验证），energy 仍被转换
        $this->assertSame(0, $data[1]['meter_id']);
        $this->assertSame(400.0, $data[1]['energy']);
    }

    /**
     * 测试数据指纹幂等
     *
     * 说明：ModuleFeatureExcelService::export 完整流程（formatRow → 引擎生成文件 →
     * AFile 保存）依赖 AFile 模块与存储配置，由命令冒烟覆盖（featureexcel:validate）；
     * 本测试覆盖其钩子应用部分（applyExportHooks）与指纹生成。
     */
    public function test_generate_data_fingerprint_consistency(): void
    {
        $fp1 = ModuleFeatureExcelService::generateDataFingerprint(
            ['tenant_id' => 1, 'date' => '2026-08-01'],
            [['a' => 1], ['b' => 2]]
        );
        $fp2 = ModuleFeatureExcelService::generateDataFingerprint(
            ['date' => '2026-08-01', 'tenant_id' => 1],
            [['a' => 1], ['b' => 2]]
        );

        $this->assertNotSame('', $fp1);
        $this->assertSame($fp1, $fp2);
    }
}
