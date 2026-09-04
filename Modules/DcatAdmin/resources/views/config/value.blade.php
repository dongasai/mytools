<div style="width: 200px" >
    @if ($model->type === \Modules\Application\Enums\CONFIG_TYPE::TYPE_BOOL->value())
        {{ $value ? '开启' : '关闭' }}
    @elseif ($model->type === \Modules\Application\Enums\CONFIG_TYPE::TYPE_IS->value())
        {{ $value ? '是' : '否' }}
    @elseif ($model->type === \Modules\Application\Enums\CONFIG_TYPE::TYPE_TIME->value())
        {{  \UCore\Helper\Carbon\CarbonInterval::secondsCascadeForHumans($value) }}
    @else
        {{ $value }}
    @endif

</div>

