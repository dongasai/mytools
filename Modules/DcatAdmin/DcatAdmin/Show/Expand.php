<?php

namespace Modules\DcatAdmin\DcatAdmin\Show;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Show\AbstractField;
use Dcat\Admin\Support\Helper;
use Illuminate\Support\Str;

class Expand extends AbstractField
{
    protected $button;

    protected static $counter = 0;

    public $callbackOrButton;

    public function button($button)
    {
        $this->button = $button;
    }

    public function callback($callbackOrButton)
    {
        $this->callbackOrButton = $callbackOrButton;

        return $this;
    }

    public function render($callbackOrButton = null)
    {
        return $this->display($callbackOrButton);
    }

    public function display($callbackOrButton = null)
    {

        $html = $this->value;
        $remoteUrl = '';

        if ($callbackOrButton && $callbackOrButton instanceof \Closure) {
            $callbackOrButton = $callbackOrButton->call($this->model, $this);

            if (! $callbackOrButton instanceof LazyRenderable) {
                $html = Helper::render($callbackOrButton);

                $callbackOrButton = null;
            }
        }

        if ($callbackOrButton instanceof LazyRenderable) {
            $html = '<div style="min-height: 150px"></div>';

            $remoteUrl = $callbackOrButton->getUrl();
        } elseif (is_string($callbackOrButton) && is_subclass_of($callbackOrButton, LazyRenderable::class)) {
            $html = '<div style="min-height: 150px"></div>';

            $renderable = $callbackOrButton::make();

            $remoteUrl = $renderable->getUrl();
        } elseif ($callbackOrButton && is_string($callbackOrButton)) {
            $this->button = $callbackOrButton;
        }

        $button = is_null($this->button) ? $this->value : $this->button;
        $data = [
            'key' => $this->getKey(),
            'url' => $remoteUrl,
            'button' => $button,
            'html' => $html,
            'dataKey' => $this->getDataKey(),
        ];

        return Admin::view('module_dcatadmin::core.show.expand', $data);
    }

    protected function getKey()
    {
        return $this->value;
    }

    protected function getDataKey()
    {
        $key = $this->value ?: Str::random(8);

        static::$counter++;

        return $this->makeName($key.'-'.static::$counter);
    }

    public function makeName($string)
    {
        return 'expand'.$string;
    }
}
