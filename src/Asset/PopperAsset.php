<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

/**
 * ============================================================
 *  Popper 资源包 —— AdminLte4\Asset\PopperAsset
 * ============================================================
 *
 * Bootstrap 5 的下拉菜单/提示气泡依赖 Popper，必须先于 bootstrap.js 加载。
 * 顺序由 `$depends` 声明，交给官方 AssetManager 递归展开，
 * 调用方不需要手工排序。
 *
 * 【双模式由父类收敛】
 *   CDN 模式：产出最终绝对 URL，`$cdn = true`，跳过发布与本地文件检查。
 *   自托管：`$cdn = false` + `sourcePath`，由官方 AssetPublisher 把
 *   **包内自带**的文件发布到 `@public/assets/vendor/<crc32>/` 并回填 baseUrl。
 *   两种模式的选择只由 `AssetUrlResolver::isLocal()` 决定。
 */
final class PopperAsset extends LocalizableAsset
{
    protected function package(): string
    {
        return '@popperjs/core';
    }

    protected function jsPaths(): array
    {
        return ['dist/umd/popper.min.js'];
    }
}
