<?php

namespace Baijunyao\LaravelPluginManager\Middleware;

use Closure;

class PluginManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // 获取 response 内容
        $content = $response->getContent();

        // 如果没有 body 标签 或者是跳转页面 直接返回
        if (false === strripos($content, '</body>') || false !== strripos($content, 'Redirecting to')) {
            return $response;
        }

        $config = config('laravel-plugin-manager.plugin');
        // 初始化要出入的 css 和 js
        $css = '';
        $js = '';
        $search = [];
        $replace = [];

        foreach ($config as $k => $v) {
            // 如果当前路由需要排除掉插件 则直接 continue
            $except = empty($v['except']) ? [] : $v['except'];

            if ($request->is($except)) {
                continue;
            }

            // 获取插件要插入的 css 和 js
            $manager = new $v['name']($request, $response);
            
            // 如果没有用到此插件 则直接 continue
            $element = $manager->getElement();

            if (false === strripos($content, $element)) {
                continue;
            }

            // 获取 css 和 js
            $css .= $manager->getCss();
            $js .= $manager->getJs();
            // 获取需要替换的内容
            $search = array_merge($search, array_column($manager->getReplace(), 'search'));
            $replace = array_merge($replace, array_column($manager->getReplace(), 'replace'));
            $manager->clean();
        }

        // 插入 css 和 js
        $css = $css . "\n\r" . '</head>';
        $js = $js . "\n\r" . '</body>';
        $search = array_merge($search, ['</head>', '</body>']);
        $replace = array_merge($replace, [$css, $js]);
        $content = str_replace($search, $replace, $content);

        // 更新内容并重置Content-Length
        $response->setContent($content);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
