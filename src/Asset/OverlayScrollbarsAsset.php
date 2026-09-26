<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

use Yiisoft\Assets\AssetBundle;

/**
 * ============================================================
 *  OverlayScrollbars 资源包 —— AdminLte4\Asset\OverlayScrollbarsAsset
 * ============================================================
 *
 * AdminLTE 4 用它替换了 v3 的 jQuery 滚动条插件，
 * 负责侧边栏的自定义滚动（无 jQuery 依赖）。
 */
final class OverlayScrollbarsAsset extends AssetBundle
{
    public bool $cdn = true;

    public array $depends = [];

    public function __construct(?AssetUrlResolver $urls = null)
    {
        $urls ??= new AssetUrlResolver();

        $this->css = [$urls->url('overlayscrollbars', 'styles/overlayscrollbars.min.css')];
        $this->js = [$urls->url('overlayscrollbars', 'browser/overlayscrollbars.browser.es6.min.js')];
    }
}
