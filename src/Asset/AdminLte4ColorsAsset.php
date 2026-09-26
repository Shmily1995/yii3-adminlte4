<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

use Yiisoft\Assets\AssetBundle;

/**
 * ============================================================
 *  AdminLTE 4 扩展调色板 —— AdminLte4\Asset\AdminLte4ColorsAsset
 * ============================================================
 *
 * 额外提供 `text-bg-navy` / `bg-purple` 等 14 色扩展类。
 * 默认不加载（params 的 `adminlte4.colors` 控制），
 * 关闭时页面只用 Bootstrap 原生主题色。
 *
 * 【顺序要求】
 *   必须在 `adminlte.css` **之后**加载，否则会被主样式覆盖。
 *   这一点由 `$depends` 保证 —— 依赖先注册，AdminLTE 主样式先输出。
 */
final class AdminLte4ColorsAsset extends AssetBundle
{
    public bool $cdn = true;

    public array $depends = [
        AdminLte4Asset::class,
    ];

    public function __construct(?AssetUrlResolver $urls = null)
    {
        $urls ??= new AssetUrlResolver();

        $this->css = [$urls->url('admin-lte', 'dist/css/adminlte-colors.css')];
        $this->js = [];
    }
}
