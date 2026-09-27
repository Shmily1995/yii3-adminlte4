<?php

declare(strict_types=1);

namespace AdminLte4\Tests\Unit;

use AdminLte4\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    #[Test]
    public function 包版本号符合语义化版本(): void
    {
        self::assertMatchesRegularExpression(
            '/^\d+\.\d+\.\d+$/',
            Config::PACKAGE_VERSION,
        );
    }

    #[Test]
    public function 默认版本表覆盖所有资源包(): void
    {
        // 6 个 AssetBundle 各自依赖的 npm 包都必须在默认表里，缺一个就会在运行时抛异常
        self::assertArrayHasKey('admin-lte', Config::DEFAULT_VERSIONS);
        self::assertArrayHasKey('bootstrap', Config::DEFAULT_VERSIONS);
        self::assertArrayHasKey('bootstrap-icons', Config::DEFAULT_VERSIONS);
        self::assertArrayHasKey('overlayscrollbars', Config::DEFAULT_VERSIONS);
        self::assertArrayHasKey('@popperjs/core', Config::DEFAULT_VERSIONS);
    }

    #[Test]
    public function 包根目录指向composer_json所在层(): void
    {
        $root = Config::rootPath();

        self::assertFileExists($root . '/composer.json');
        self::assertFileExists($root . '/resources/views/layout.php');
    }

    #[Test]
    public function 视图目录位于包根之下(): void
    {
        self::assertSame(Config::rootPath() . '/resources/views', Config::viewsPath());
    }
}
