<?php

declare(strict_types=1);

namespace AdminLte4\Injection;

use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;
use Yiisoft\Yii\View\Renderer\LayoutParametersInjectionInterface;

/**
 * ============================================================
 *  AdminLTE 公共参数注入 —— AdminLte4\Injection\AdminLteInjection
 * ============================================================
 *
 * 【它解决什么】
 *   用官方 `yiisoft/yii-view-renderer` 渲染时，ViewRenderer 会自动收集
 *   所有实现了 `*InjectionInterface` 的对象，把它们的返回值合并进
 *   视图参数。这样「每个页面都要带的公共变量」就不用在每个 Action 里
 *   手写一遍，而是**声明式**地交给注入。
 *
 *   本类把 AdminLTE 那组「站点级配置」翻译成注入参数：
 *   brand（品牌）、menu（侧边栏）、home / homeUrl（面包屑首页）。
 *
 * 【和 AdminLteRenderer 的关系】
 *   AdminLteRenderer 是「一条龙」：注册资源 + 解析菜单 + 渲染布局 + 替换占位符。
 *   本类只做其中极小的一块 —— 提供公共参数。二者不冲突：
 *     · 用 AdminLteRenderer 的应用 → 直接调 $renderer->render()，不需要本类
 *     · 用官方 ViewRenderer + 自定义布局的应用 → 把本类加进 DI，
 *       即可在布局里拿到 $brand / $menu / $home / $homeUrl
 *
 * 【红线】本类**不碰** CSRF。
 *   官方 yii-view-renderer 自带的 CsrfViewInjection 依赖 yiisoft/csrf 的
 *   会话态令牌体系；本项目的自研方案是「按 jti 隔离 + 每次渲染重签发」，
 *   二者语义不同，不要混用。CSRF 仍由应用自己的中间件与渲染器处理。
 *
 * @param array<string,mixed> $config params 里的 'adminlte4' 参数组
 */
final class AdminLteInjection implements
    CommonParametersInjectionInterface,
    LayoutParametersInjectionInterface
{
    public function __construct(
        private readonly array $config = [],
    ) {
    }

    /**
     * 页面模板与布局**都**可见的参数。
     *
     * @return array<string,mixed>
     */
    public function getCommonParameters(): array
    {
        return [
            'brand' => (string) ($this->config['brand'] ?? 'Admin'),
            'home' => (string) ($this->config['home'] ?? ''),
            'homeUrl' => (string) ($this->config['homeUrl'] ?? '/'),
            // 菜单原样透传：数组或 Menu 实例皆可，由使用方决定怎么渲染。
            'menu' => $this->config['menu'] ?? [],
        ];
    }

    /**
     * 只在布局可见的参数。
     *
     * @return array<string,mixed>
     */
    public function getLayoutParameters(): array
    {
        $options = (array) ($this->config['options'] ?? []);

        return [
            'bodyClass' => (string) ($options['bodyClass'] ?? ''),
            'sidebarDark' => (bool) ($options['sidebarDark'] ?? false),
            'fixedHeader' => (bool) ($options['fixedHeader'] ?? true),
            'scrollToTop' => (bool) ($options['scrollToTop'] ?? false),
        ];
    }
}
