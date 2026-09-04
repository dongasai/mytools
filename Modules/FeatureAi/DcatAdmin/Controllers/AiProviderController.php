<?php

namespace Modules\FeatureAi\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureAi\DcatAdmin\Repositories\AiProviderRepository;
use Modules\FeatureAi\Enums\AiProviderType;

/**
 * AI供应商管理
 */
class AiProviderController extends AdminController
{
    protected $title = 'AI供应商管理';

    protected $description = '管理AI服务供应商配置，支持OpenAI/Claude/Gemini/Deepseek/MiniMax等驱动';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new AiProviderRepository, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('provider_type', '驱动类型')->width('120px')->using([
                'openai' => '<span class="badge" style="background:#3b82f6;color:#fff">OpenAI</span>',
                'claude' => '<span class="badge" style="background:#6366f1;color:#fff">Claude</span>',
                'gemini' => '<span class="badge" style="background:#10b981;color:#fff">Gemini</span>',
                'deepseek' => '<span class="badge" style="background:#f59e0b;color:#000">Deepseek</span>',
                'zai' => '<span class="badge" style="background:#ef4444;color:#fff">智谱AI</span>',
                'minimax' => '<span class="badge" style="background:#8b5cf6;color:#fff">MiniMax</span>',
                'mistral' => '<span class="badge" style="background:#14b8a6;color:#fff">Mistral</span>',
                'ollama' => '<span class="badge" style="background:#64748b;color:#fff">Ollama</span>',
                'cohere' => '<span class="badge" style="background:#06b6d4;color:#fff">Cohere</span>',
                'xai' => '<span class="badge" style="background:#1f2937;color:#fff">X.AI</span>',
                'aws' => '<span class="badge" style="background:#f97316;color:#fff">AWS</span>',
                'huggingface' => '<span class="badge" style="background:#ec4899;color:#fff">HuggingFace</span>',
                'elevenlabs' => '<span class="badge" style="background:#84cc16;color:#000">ElevenLabs</span>',
                'custom' => '<span class="badge" style="background:#94a3b8;color:#000">自定义</span>',
            ]);
            $grid->column('provider_name', '供应商名称')->width('200px');
            $grid->column('api_endpoint', 'API端点')->width('250px');
            $grid->column('api_key', 'API密钥')->width('200px')->display(function ($value) {
                if (empty($value)) {
                    return '-';
                }
                return substr($value, 0, 7) . '***' . substr($value, -4);
            });
            $grid->column('is_active', '是否启用')->switch()->width('100px');
            $grid->column('priority', '优先级')->sortable()->width('100px');
            $grid->column('created_at', '创建时间')->width('160px');

            // 行操作
            $grid->actions(function (\Modules\DcatAdmin\DcatAdmin\Grid\Displayers\LineActions $actions) {
                // 添加复制按钮
                $actions->append(new \Modules\FeatureAi\DcatAdmin\Actions\DuplicateProvider());
            });

            // 高级筛选
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->equal('id', 'ID');
                $filter->equal('provider_type', '驱动类型')->select([
                    'openai' => 'OpenAI',
                    'claude' => 'Claude',
                    'gemini' => 'Gemini',
                    'deepseek' => 'Deepseek',
                    'zai' => '智谱AI (GLM)',
                    'minimax' => 'MiniMax',
                    'mistral' => 'Mistral',
                    'ollama' => 'Ollama',
                    'cohere' => 'Cohere',
                    'xai' => 'X.AI (Grok)',
                    'aws' => 'AWS Bedrock',
                    'huggingface' => 'HuggingFace',
                    'elevenlabs' => 'ElevenLabs',
                    'custom' => '自定义',
                ]);
                $filter->like('provider_name', '供应商名称');
                $filter->equal('is_active', '是否启用')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);
                $filter->between('created_at', '创建时间')->datetime();
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     */
    protected function detail($id): Show
    {
        return Show::make($id, new AiProviderRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('provider_type', '驱动类型')->using([
                'openai' => 'OpenAI',
                'claude' => 'Claude',
                'gemini' => 'Gemini',
                'deepseek' => 'Deepseek',
                'zai' => '智谱AI (GLM)',
                'minimax' => 'MiniMax',
                'mistral' => 'Mistral',
                'ollama' => 'Ollama',
                'cohere' => 'Cohere',
                'xai' => 'X.AI (Grok)',
                'aws' => 'AWS Bedrock',
                'huggingface' => 'HuggingFace',
                'elevenlabs' => 'ElevenLabs',
                'custom' => '自定义',
            ]);
            $show->field('provider_name', '供应商名称');
            $show->field('api_key', 'API密钥')->as(function ($value) {
                if (empty($value)) {
                    return '-';
                }
                return substr($value, 0, 7) . '***' . substr($value, -4);
            });
            $show->field('api_endpoint', 'API端点');
            $show->field('is_active', '是否启用')->using([
                1 => '启用',
                0 => '禁用',
            ]);
            $show->field('priority', '优先级');
            $show->field('config_json', '运行时配置')->json();
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new AiProviderRepository, function (Form $form) {
            $form->display('id', 'ID');

            // 准备provider_type选项（排除CUSTOM）
            $providerOptions = [];
            foreach (AiProviderType::cases() as $case) {
                if ($case !== AiProviderType::CUSTOM) {
                    $providerOptions[$case->value] = $case->getName();
                }
            }

            $form->select('provider_type', '驱动类型')
                ->required()
                ->options($providerOptions)
                ->help('选择AI服务提供商驱动');

            $form->text('provider_name', '供应商名称')
                ->required()
                ->rules('required|string|max:100')
                ->help('供应商的显示名称');

            $form->text('api_key', 'API密钥')
                ->required()
                ->help('API密钥明文存储，请妥善保管');

            $form->url('api_endpoint', 'API端点')
                ->help('可选，自定义API端点URL');

            $form->switch('is_active', '是否启用')
                ->default(1)
                ->help('启用后该供应商可用于AI服务');

            $form->number('priority', '优先级')
                ->default(0)
                ->help('数字越大优先级越高');

            $form->textarea('config_json', '运行时配置')
                ->help('JSON格式运行时参数，如：{"timeout": 30}')
                ->rules('nullable|json')
                ->attribute('rows', 4);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 保存前验证JSON格式
            $form->saving(function (Form $form) {
                $configJson = $form->input('config_json');
                if (!empty($configJson) && is_string($configJson)) {
                    json_decode($configJson);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $form->response()->error('运行时配置必须是有效的JSON格式');
                    }
                }
            });
        });
    }

    /**
     * Get the title of the controller.
     */
    public function title(): string
    {
        return $this->title;
    }
}
