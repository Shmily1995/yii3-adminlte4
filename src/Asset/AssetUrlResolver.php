<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

use AdminLte4\Config;
use InvalidArgumentException;

/**
 * ============================================================
 *  资源 URL 解析器 —— AdminLte4\Asset\AssetUrlResolver
 * ============================================================
 *
 * 【为什么要有这一层】
 *   同一个文件在不同部署环境下的 URL 完全不同：
 *     默认       ：https://cdn.jsdelivr.net/npm/admin-lte@4.9.1/dist/css/adminlte.min.css
 *     fastly 节点：https://fastly.jsdelivr.net/npm/admin-lte@4.9.1/dist/css/adminlte.min.css
 *     unpkg      ：https://unpkg.com/admin-lte@4.9.1/dist/css/adminlte.min.css
 *     完全自托管 ：/assets/vendor/admin-lte/dist/css/adminlte.min.css
 *   如果把 URL 写死在各个 AssetBundle 里，换环境就要改代码。
 *   把差异收敛到这一个类 + 一个 params 开关，换环境只改配置。
 *
 * 【关于国内可用性的一点提醒】
 *   jsdelivr 在国内时有波动（DNS 污染 / 被限速），表现为
 *   「页面结构正常但样式全丢，且加载要卡十几秒」。
 *   面向国内用户的**生产环境建议用 local**（resources/bin/fetch-assets.sh
 *   一键把固定版本抓到自己站点），彻底不依赖外网。
 */
final class AssetUrlResolver
{
    public const JSDELIVR = 'jsdelivr';
    public const JSDELIVR_FASTLY = 'jsdelivr-fastly';
    public const UNPKG = 'unpkg';
    public const LOCAL = 'local';

    private const PROVIDERS = [self::JSDELIVR, self::JSDELIVR_FASTLY, self::UNPKG, self::LOCAL];

    /**
     * 各提供方的 URL 模板（占位符 {pkg} / {ver} / {path}）。
     *
     * 【为什么用模板表，而不是一堆 if】
     *   加一个源只需要加一行，且「有哪些源」一眼可枚举。
     *
     * ⚠️【实测结论（2026-09）—— 曾经的 npmmirror 已被移除】
     *   · registry.npmmirror.com/{pkg}/{ver}/files/{path} → **403**
     *   · cdn.npmmirror.com/{pkg}/{ver}/{path}            → **404**
     *   · npm.elemecdn.com/{pkg}@{ver}/{path}             → **404**
     *   三者当时都取不到 admin-lte 4.9.1 的 CSS。
     *   保留一个「配了就必然 403」的源，代价是**用户照文档切了国内源之后
     *   样式全丢**，且从页面上看不出是 CDN 问题（HTML 结构完全正常）。
     *   所以直接移除，并把国内场景引导到 local 自托管。
     *
     *   另：cdn.staticfile.org 可用，但它的路径形态与 npm 不一致
     *   （/{lib}/{ver}/{path}），且各包收录情况不一，不适合做成通用模板。
     */
    private const TEMPLATES = [
        self::JSDELIVR => 'https://cdn.jsdelivr.net/npm/{pkg}@{ver}/{path}',
        self::JSDELIVR_FASTLY => 'https://fastly.jsdelivr.net/npm/{pkg}@{ver}/{path}',
        self::UNPKG => 'https://unpkg.com/{pkg}@{ver}/{path}',
    ];

    /**
     * @param string               $provider      资源提供方，见上面的常量
     * @param string               $localBaseUrl  provider = local 时的资源根 URL
     * @param array<string,string> $versions      npm 包名 => 版本号，缺省回落到 Config::DEFAULT_VERSIONS
     * @param string               $localBasePath provider = local 时，把包内资源「发布」到的目标目录别名
     *                                            （用于 yiisoft/assets 的 AssetPublisher）
     */
    public function __construct(
        private readonly string $provider = self::JSDELIVR,
        private readonly string $localBaseUrl = '/assets/vendor',
        private readonly array $versions = [],
        private readonly string $localBasePath = '@public/assets/vendor',
    ) {
        if (!in_array($this->provider, self::PROVIDERS, true)) {
            throw new InvalidArgumentException(sprintf(
                '未知的资源提供方 "%s"，可选值：%s',
                $this->provider,
                implode('、', self::PROVIDERS),
            ));
        }
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function isLocal(): bool
    {
        return $this->provider === self::LOCAL;
    }

    /**
     * 本地（自托管）模式下，某个 npm 包资源在**包内**的根目录。
     *
     * 包自带资源文件（resources/assets/vendor/），自托管时由 AssetPublisher
     * 从该目录发布到应用的 public/ 下，从而实现「离线可用、不依赖外网 CDN」。
     */
    public function localSourcePath(string $package): string
    {
        return Config::rootPath() . '/resources/assets/vendor/' . $package;
    }

    /**
     * 自托管模式下，资源被发布到的**目录别名**（如 '@public/assets/vendor'）。
     */
    public function localBasePath(): string
    {
        return $this->localBasePath;
    }

    /**
     * 自托管模式下，资源被发布后的**根 URL**（如 '/assets/vendor'）。
     */
    public function localBaseUrl(): string
    {
        return $this->localBaseUrl;
    }

    /**
     * 取某个 npm 包的版本号。
     *
     * 先查配置覆盖，再回落包内默认表。两者都没有就抛异常 ——
     * 静默用空版本号会拼出 `admin-lte@/...` 这种坏 URL，
     * 表现为「资源 404 但页面不报错」，极难排查。
     */
    public function version(string $package): string
    {
        $version = $this->versions[$package] ?? Config::DEFAULT_VERSIONS[$package] ?? null;

        if ($version === null || $version === '') {
            throw new InvalidArgumentException(sprintf(
                '未为 npm 包 "%s" 配置版本号。请在 params 的 adminlte4.versions 中补上。',
                $package,
            ));
        }

        return $version;
    }

    /**
     * 解析出「某个 npm 包内某个文件」的最终 URL。
     *
     * @param string      $package npm 包名，如 'admin-lte'、'@popperjs/core'
     * @param string      $path    包内相对路径，如 'dist/css/adminlte.min.css'
     * @param string|null $version 显式指定版本；null 表示走 version() 的解析规则
     */
    public function url(string $package, string $path, ?string $version = null): string
    {
        $path = ltrim($path, '/');

        // local 模式不带版本号：文件放哪就是你自己的事，
        // 由 resources/bin/fetch-assets.sh 负责按固定版本抓取到位。
        if ($this->isLocal()) {
            return rtrim($this->localBaseUrl, '/') . '/' . $package . '/' . $path;
        }

        $version ??= $this->version($package);

        $template = self::TEMPLATES[$this->provider]
            ?? throw new InvalidArgumentException(sprintf(
                '资源提供方 "%s" 没有配置 URL 模板。',
                $this->provider,
            ));

        return str_replace(
            ['{pkg}', '{ver}', '{path}'],
            [$package, $version, $path],
            $template,
        );
    }
}
