<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

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
 *
 * 【自托管时字体文件也要一起发布】
 *   `bootstrap-icons.min.css` 内部以 `url("fonts/bootstrap-icons.woff2?hash")`
 *   相对引用字体。包内 `resources/assets/vendor/bootstrap-icons/`
 *   已保留 `font/fonts/` 这一层结构，发布后相对路径依然成立。
 */
final class BootstrapIconsAsset extends LocalizableAsset
{
    protected function package(): string
    {
        return 'bootstrap-icons';
    }

    protected function cssPaths(): array
    {
        return ['font/bootstrap-icons.min.css'];
    }
}
