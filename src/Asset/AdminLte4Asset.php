<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

use Yiisoft\Assets\AssetBundle;

/**
 * ============================================================
 *  AdminLTE 4 主体资源包 —— AdminLte4\Asset\AdminLte4Asset
 * ============================================================
 *
 * 【用法（官方标准写法）】
 *   $assetManager->register(AdminLte4Asset::class);
 *
 *   注册这一个包，就等于把官方 Introduction 里那份 CDN 清单完整引进来，
 *   依赖由 AssetManager 递归展开，顺序无需手工维护。
 *
 * 【最终加载顺序（由 $depends 的后序遍历决定）】
 *   CSS：bootstrap-icons → overlayscrollbars → adminlte
 *   JS ：popper → bootstrap → overlayscrollbars → adminlte
 *
 * 【要升级版本时改哪里】
 *   应用 params 的 `adminlte4.versions`，或换 `adminlte4.cdn` 提供方。
 *   不要在类里写死版本号。
 */
final class AdminLte4Asset extends AssetBundle
{
    public bool $cdn = true;

    public array $depends = [
        BootstrapIconsAsset::class,
        Bootstrap5Asset::class,
        OverlayScrollbarsAsset::class,
    ];

    public function __construct(?AssetUrlResolver $urls = null)
    {
        $urls ??= new AssetUrlResolver();

        $this->css = [$urls->url('admin-lte', 'dist/css/adminlte.min.css')];
        $this->js = [$urls->url('admin-lte', 'dist/js/adminlte.min.js')];
    }
}
