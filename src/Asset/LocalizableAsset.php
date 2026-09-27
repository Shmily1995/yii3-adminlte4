<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

use Yiisoft\Assets\AssetBundle;

/**
 * ============================================================
 *  可「CDN / 自托管」双模式的资源包基类 —— LocalizableAsset
 * ============================================================
 *
 * 【它把两套模式收敛到一个分支】
 *   · CDN 模式（provider = jsdelivr / jsdelivr-fastly / unpkg）
 *       → $cdn = true，$css/$js 是**完整 URL**（由 AssetUrlResolver 生成）
 *   · 自托管模式（provider = local）
 *       → $cdn = false，$sourcePath 指向**包内自带资源**，
 *         $basePath/$baseUrl 是发布目标（由 AssetPublisher 在 register 时
 *         把 sourcePath 拷贝过去，并回填真正的 [path, url]）
 *
 * 【为什么自托管要走 publish 而不是写死 URL】
 *   包内资源在 vendor/ 里，web 服务器不直接可访问；只有「发布」到
 *   public/ 下才会有一条真实 URL。这正是 yiisoft/assets 的 AssetPublisher
 *   干的事 —— 于是「composer require → 自托管 → 离线可用」一条龙，
 *   不需要应用手动 fetch / 拷贝。
 *
 * 【子类只需声明三件事】
 *   package()  → npm 包名（如 'admin-lte' / '@popperjs/core'）
 *   cssPaths() → 相对包根的 CSS 文件列表
 *   jsPaths()  → 相对包根的 JS 文件列表
 *   $depends   → 依赖的其它资源包类名（沿用 AssetBundle 属性）
 */
abstract class LocalizableAsset extends AssetBundle
{
    public function __construct(?AssetUrlResolver $urls = null)
    {
        $urls ??= new AssetUrlResolver();

        if ($urls->isLocal()) {
            $this->cdn = false;
            $this->sourcePath = $urls->localSourcePath($this->package());
            $this->basePath = $urls->localBasePath();
            $this->baseUrl = $urls->localBaseUrl();
            $this->css = $this->cssPaths();
            $this->js = $this->jsPaths();

            return;
        }

        $this->cdn = true;
        $this->css = $this->toUrls($urls, $this->cssPaths());
        $this->js = $this->toUrls($urls, $this->jsPaths());
    }

    /**
     * 本资源包对应的 npm 包名。
     */
    abstract protected function package(): string;

    /**
     * 相对包根的 CSS 文件路径列表。
     *
     * @return list<string>
     */
    protected function cssPaths(): array
    {
        return [];
    }

    /**
     * 相对包根的 JS 文件路径列表。
     *
     * @return list<string>
     */
    protected function jsPaths(): array
    {
        return [];
    }

    /**
     * @param list<string> $paths
     *
     * @return list<string>
     */
    private function toUrls(AssetUrlResolver $urls, array $paths): array
    {
        // ⚠️ 这里不能用 `static fn`：闭包内需要 $this->package()，
        //    static 闭包没有 $this 绑定，会直接抛
        //    "Using $this when not in object context"。
        $package = $this->package();

        return array_map(
            static fn (string $path): string => $urls->url($package, $path),
            $paths,
        );
    }
}
