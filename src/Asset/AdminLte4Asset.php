<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

/**
 * ============================================================
 *  AdminLTE 4 主体资源包
 * ============================================================
 *
 * 注册这一个包，就等于把官方 Introduction 里那份 CDN 清单完整引进来，
 * 依赖由 AssetManager 递归展开，顺序无需手工维护。
 *
 * 最终加载顺序（由 $depends 的后序遍历决定）：
 *   CSS：bootstrap-icons → overlayscrollbars → adminlte
 *   JS ：popper → bootstrap → overlayscrollbars → adminlte
 *
 * 双模式（CDN / 自托管）由 LocalizableAsset 基类统一处理，
 * 切换只需改 params 的 adminlte4.cdn。
 */
final class AdminLte4Asset extends LocalizableAsset
{
    public array $depends = [
        BootstrapIconsAsset::class,
        Bootstrap5Asset::class,
        OverlayScrollbarsAsset::class,
    ];

    protected function package(): string
    {
        return 'admin-lte';
    }

    protected function cssPaths(): array
    {
        return ['dist/css/adminlte.min.css'];
    }

    protected function jsPaths(): array
    {
        return ['dist/js/adminlte.min.js'];
    }
}
