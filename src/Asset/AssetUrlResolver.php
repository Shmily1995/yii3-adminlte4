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
 *   同一个文件在三种部署环境下的 URL 完全不同：
 *     开发/演示 ：https://cdn.jsdelivr.net/npm/admin-lte@4.9.1/dist/css/adminlte.min.css
 *     国内生产  ：https://registry.npmmirror.com/admin-lte/4.9.1/files/dist/css/adminlte.min.css
 *     完全自托管：/assets/vendor/admin-lte/dist/css/adminlte.min.css
 *   如果把 URL 写死在各个 AssetBundle 里，换环境就要改代码。
 *   把差异收敛到这一个类 + 一个 params 开关，换环境只改配置。
 *
 * 【关于国内可用性的一点提醒】
 *   jsdelivr 在国内时有波动（DNS 污染 / 被限速），表现为
 *   「页面结构正常但样式全丢，且加载要卡十几秒」。
 *   面向国内用户的后台建议直接用 npmmirror 或 local。
 */
final class AssetUrlResolver
{
    public const JSDELIVR = 'jsdelivr';
    public const NPMMIRROR = 'npmmirror';
    public const LOCAL = 'local';

    private const PROVIDERS = [self::JSDELIVR, self::NPMMIRROR, self::LOCAL];

    /**
     * @param string               $provider     资源提供方，见上面的常量
     * @param string               $localBaseUrl provider = local 时的资源根 URL
     * @param array<string,string> $versions     npm 包名 => 版本号，缺省回落到 Config::DEFAULT_VERSIONS
     */
    public function __construct(
        private readonly string $provider = self::JSDELIVR,
        private readonly string $localBaseUrl = '/assets/vendor',
        private readonly array $versions = [],
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

        return $this->provider === self::NPMMIRROR
            ? sprintf('https://registry.npmmirror.com/%s/%s/files/%s', $package, $version, $path)
            : sprintf('https://cdn.jsdelivr.net/npm/%s@%s/%s', $package, $version, $path);
    }
}
