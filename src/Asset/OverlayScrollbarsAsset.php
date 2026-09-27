<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

/**
 * ============================================================
 *  OverlayScrollbars 资源包 —— AdminLte4\Asset\OverlayScrollbarsAsset
 * ============================================================
 *
 * AdminLTE 4 用它替换了 v3 的 jQuery 滚动条插件，
 * 负责侧边栏的自定义滚动（无 jQuery 依赖）。
 */
final class OverlayScrollbarsAsset extends LocalizableAsset
{
    protected function package(): string
    {
        return 'overlayscrollbars';
    }

    protected function cssPaths(): array
    {
        return ['styles/overlayscrollbars.min.css'];
    }

    protected function jsPaths(): array
    {
        return ['browser/overlayscrollbars.browser.es6.min.js'];
    }
}
