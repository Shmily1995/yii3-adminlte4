<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

use Yiisoft\Assets\AssetBundle;

/**
 * ============================================================
 *  Popper 资源包 —— AdminLte4\Asset\PopperAsset
 * ============================================================
 *
 * Bootstrap 5 的下拉菜单/提示气泡依赖 Popper，必须先于 bootstrap.js 加载。
 * 顺序由 `$depends` 声明，交给官方 AssetManager 递归展开，
 * 调用方不需要手工排序。
 *
 * 【为什么 `$cdn` 恒为 true】
 *   本包构造时产出的已经是**最终 URL**（绝对 URL 或 `/` 开头的根相对路径），
 *   不需要 AssetManager 再做「把文件发布到 public/」这一步。
 *   官方 `$cdn` 的语义正是「跳过发布与本地文件检查」，
 *   所以无论走 CDN 还是自托管都设为 true。
 */
final class PopperAsset extends AssetBundle
{
    public bool $cdn = true;

    public array $depends = [];

    /**
     * @param AssetUrlResolver|null $urls 不传时用默认提供方（jsdelivr + 内置版本表），
     *                                    保证「装了就能用」的零配置体验。
     */
    public function __construct(?AssetUrlResolver $urls = null)
    {
        $urls ??= new AssetUrlResolver();

        $this->css = [];
        $this->js = [$urls->url('@popperjs/core', 'dist/umd/popper.min.js')];
    }
}
