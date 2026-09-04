<?php

namespace Modules\DcatAdmin\DcatAdmin\Traits;

use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Content;

trait ResController
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $res = $this->callContent($content);
        if ($res) {
            return $res;
        }

        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function show($id, Content $content)
    {
        $res = $this->callContent($content);
        if ($res) {
            return $res;
        }

        return $content
            ->translation($this->translation())
            ->body($this->detail($id))
            ->title($this->title())
            ->description($this->description()['show'] ?? trans('admin.show'));
    }

    /**
     * Edit interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $res = $this->callContent($content);
        if ($res) {
            return $res;
        }

        return $content
            ->translation($this->translation())
            ->body($this->form()->edit($id)->title($this->title()))
            ->title($this->title())
            ->description($this->description()['edit'] ?? trans('admin.edit'));
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Content $content)
    {
        $res = $this->callContent($content);
        if ($res) {
            return $res;
        }

        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['create'] ?? trans('admin.create'))
            ->body($this->form());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        return $this->form()->update($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        return $this->form()->store();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->form()->destroy($id);
    }

    public function callContent(Content &$content)
    {

        if (request()->header('Sec-Fetch-Dest') == 'iframe' || request('in_iframe')) {
            $content->full();
            Admin::disablePjax();
        }

        if (request()->header('Sec-Fetch-Dest') == 'iframe' && ! request('in_iframe')) {
            return admin_redirect('/iframe?to='.url()->current());
        }
    }
}
