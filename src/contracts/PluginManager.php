<?php

namespace Baijunyao\LaravelPluginManager\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class PluginManager
{
    /**
     * Illuminate\Http\Request
     *
     * @var Request
     */
    protected $request;

    /**
     * Illuminate\Http\Response
     *
     * @var Response
     */
    protected $response;

    /**
     * html 内容
     *
     * @var string
     */
    protected $content;

    /**
     * 排除的路由
     *
     * @var array
     */
    protected $except = [];

    /**
     * css
     *
     * @var array
     */
    protected $css = [];

    /**
     * js
     *
     * @var array
     */
    protected $js = [];

    /**
     * 前端使用的标签 class 名或者 id 名
     *
     * @var string
     */
    protected $element = '';

    /**
     * 需要替换的内容
     *
     * @var array
     */
    protected $replace = [];

    /**
     * css js 以及替换的内容需要在此方法中添加
     *
     * @return mixed
     */
    abstract protected function load();

    /**
     * PluginManager constructor.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->content = $response->getContent();
        if ($this->verify()) {
            $this->load();
        }
    }

    /**
     * 获取 html 内容
     *
     * @return string
     */
    protected function getContent()
    {
        return $this->content;
    }

    /**
     * 获取 css
     *
     * @return string
     */
    public function getCss()
    {
        return implode("\n\r", $this->css);
    }

    /**
     * 获取 js
     *
     * @return string
     */
    public function getJs()
    {
        return implode("\n\r", $this->js);
    }

    /**
     * 获取 class 名
     *
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * 获取需要替换的内容
     *
     * @return array
     */
    public function getReplace()
    {
        return $this->replace;
    }

    /**
     * 设置替换的内容
     *
     * @param array $data
     *
     * @return array
     */
    public function setReplace(array $data)
    {
        return $this->replace[] = $data;
    }

    /**
     * 验证是否需要使用插件  返回 true 表示使用插件
     *
     * @return bool
     */
    protected function verify(){
        return true;
    }

    /**
     * 设置 css 文件
     *
     * @param $path
     *
     * @return $this
     */
    protected function cssFile($path){
        if (is_array($path)) {
            foreach ($path as $k => $v) {
                $href = asset($v);
                $this->css[] = "<link href=\"$href\" rel=\"stylesheet\" type=\"text/css\" />";
            }
        } else {
            $href = asset($path);
            $this->css[] = "<link href=\"$href\" rel=\"stylesheet\" type=\"text/css\" />";
        }
        return $this;
    }

    /**
     * 设置 css 内容
     *
     * @param $content
     *
     * @return $this
     */
    protected function cssContent($content){
        // 判断是否需要手动加 script 标签
        if (false === strripos($content, '<link') && false === strripos($content, '<style')) {
            $this->css[] = <<<css
<style>
$content
</style>
css;
        } else {
            $this->css[] = $content;
        }

        return $this;
    }

    /**
     * 设置 js 文件
     *
     * @param $path
     *
     * @return $this
     */
    protected function jsFile($path){
        if (is_array($path)) {
            foreach ($path as $k => $v) {
                $src = asset($v);
                $this->js[] = "<script src=\"$src\"></script>";
            }
        } else {
            $src = asset($path);
            $this->js[] = "<script src=\"$src\"></script>";
        }

        return $this;
    }

    /**
     * 设置 js 内容
     *
     * @param $content
     *
     * @return $this
     */
    protected function jsContent($content){
        // 判断是否需要手动加 script 标签
        if (false === strripos($content, '<script')) {
            $this->js[] = <<<js
<script>
$content
</script>
js;
        } else {
            $this->js[] = $content;
        }

        return $this;
    }

    /**
     * 增加 jquery 标签
     *
     * @param null $path
     *
     * @return $this
     */
    protected function jquery($path = null){
        $path = is_null($path) ? asset('statics/jquery-2.2.4/jquery.min.js') : $path;
        $this->js[] = <<<php
<script>
    (function(){
        window.jQuery || document.write('<script src="$path"><\/script>');
    })();
</script>
php;
        return $this;
    }


}