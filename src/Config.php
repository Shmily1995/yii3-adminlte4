<?php

declare(strict_types=1);

namespace AdminLte4;

/**
 * ============================================================
 *  包级常量与默认值 —— AdminLte4\Config
 * ============================================================
 *
 * 这里只放「不会随运行环境变化」的事实：版本号、目录位置。
 * 可配置项一律走 params 组的 'adminlte4' 键。
 */
final class Config
{
    /**
     * 本包自身的版本号（便于调试时确认加载的是哪一版）。
     * 与 composer.json 的 version 字段无强绑定，升级时同步维护即可。
     */
    public const PACKAGE_VERSION = '2.0.0';

    /**
     * 默认依赖版本 —— 逐项取自 AdminLTE 4 官方文档的 CDN 安装片段。
     *
     * ⚠️ 只升 admin-lte 而不核对 bootstrap 是常见踩坑点：
     *   AdminLTE 各小版本对 Bootstrap 的 patch 版本有要求，
     *   混搭可能出现「样式正常但交互组件失灵」这类难查的问题。
     */
    public const DEFAULT_VERSIONS = [
        'admin-lte' => '4.9.1',
        'bootstrap' => '5.3.8',
        'bootstrap-icons' => '1.13.1',
        'overlayscrollbars' => '2.11.0',
        '@popperjs/core' => '2.11.8',
    ];

    /**
     * 包根目录（即含 composer.json 的那一层）。
     *
     * 用 __DIR__ 反推而不是写绝对路径 —— 包被安装到任意 vendor 目录下都能工作，
     * 这也是 composer 包的硬性要求（绝不能假设自己被放在哪）。
     */
    public static function rootPath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * 包内视图根目录。
     *
     * 布局模板从这里解析，通过**绝对路径**交给应用的 WebView 渲染 ——
     * 因此不需要把包内视图复制进应用，也不必修改应用的 view.basePath。
     */
    public static function viewsPath(): string
    {
        return self::rootPath() . '/resources/views';
    }

    private function __construct()
    {
    }
}
