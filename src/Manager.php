<?php
namespace Baijunyao\LaravelMiddlewareManager;

use Closure;
use Illuminate\Support\Str;

class Manager
{
    private $except = [];
    private $css = [];
    private $js = [];
    private $className = '';
    private $content = '';
    private $response;
    private $request;

    /**
     * Manager constructor.
     *
     * @param         $request
     * @param Closure $next
     */
    public function __construct($request, Closure $next)
    {
        $this->request = $request;
        $response = $next($request);
        $this->response = $response;
        // 获取response内容
        $this->content = $response->getContent();
    }

    /**
     * 验证是否需要使用插件  返回 true 表示使用插件
     *
     * @return bool
     */
    public function verify(){

        // 如果没有 body 标签直接返回
        if (false === strripos($this->content, '</body>')) {
            return false;
        }

        // 如果没有用到 插件 直接返回
        if (!empty($this->className) && false === strripos($this->content, $this->className)) {
            return false;
        }

        // 跳过排除的路由
        foreach ($this->except as $k => $v) {
            if (Str::is(trim($v, '/'), $this->request->path())) {
                return false;
            }
        }

        return true;
    }

    /**
     * 设置 class 名
     *
     * @param $className
     *
     * @return $this
     */
    public function className($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * 设置 css 文件
     *
     * @param $path
     *
     * @return $this
     */
    public function cssFile($path){
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
    public function cssContent($content){
        // 判断是否需要手动加 script 标签
        if (false === strripos($content, '<link')) {
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
    public function jsFile($path){
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
    public function jsContent($content){
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
    public function jQuery($path = null){
        $path = is_null($path) ? asset('statics/jquery-2.2.4/jquery.min.js') : $path;
        $this->js[] = <<<php
    (function(){
        window.jQuery || document.write('<script src="$path"><\/script>');
    })();
php;
        return $this;
    }

    /**
     * 排除的路由
     *
     * @param $route
     *
     * @return $this
     */
    public function except($route)
    {
        if (is_array($route)) {
            $this->except = array_merge($this->except, $route);
        } else {
            $this->except[] = $route;
        }
        return $this;
    }

    /**
     * 获取插入 css 和 js 后的 response ；
     *
     * @return mixed
     */
    public function response(){
        $css = implode("\n\r", $this->css)."\n\r".'</head>';
        $js = implode("\n\r", $this->js)."\n\r".'</body>';

        $seach = [
            '</head>',
            '</body>'
        ];
        $subject = [
            $css,
            $js
        ];
        $content = str_replace($seach, $subject, $this->content);

        // 更新内容并重置Content-Length
        $this->response->setContent($content);
        $this->response->headers->remove('Content-Length');
        return $this->response;
    }
}
