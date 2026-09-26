<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

use Yiisoft\Assets\AssetBundle;

/**
 * ============================================================
 *  Bootstrap Icons 资源包 —— AdminLte4\Asset\BootstrapIconsAsset
 * ============================================================
 *
 * AdminLTE 4 全面改用 Bootstrap Icons（图标类名 `bi bi-xxx`）。
 *
 * ⚠️ 从 AdminLTE 3 迁移时最容易踩的坑：
 *    v3 用 Font Awesome（`fas fa-xxx`），v4 换成 Bootstrap Icons。
 *    类名写错的表现是「图标位置空白、控制台无报错」——
 *    因为字体文件能加载，只是字形不存在。
 */
final class BootstrapIconsAsset extends AssetBundle
{
    public bool $cdn = true;

    public array $depends = [];

    public function __construct(?AssetUrlResolver $urls = null)
    {
        $urls ??= new AssetUrlResolver();

        $this->css = [$urls->url('bootstrap-icons', 'font/bootstrap-icons.min.css')];
        $this->js = [];
    }
}
