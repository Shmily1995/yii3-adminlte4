<?php

declare(strict_types=1);

namespace AdminLte4\Tests\Unit\Asset;

use AdminLte4\Asset\AssetUrlResolver;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * AssetUrlResolver 是「把 npm 包内文件解析成最终 URL」的唯一出口，
 * 换 CDN 源只改 params 不改代码 —— 这里的用例把四种提供方的模板
 * 与异常路径钉死，防止改坏后上线才发现资源全 404。
 */
final class AssetUrlResolverTest extends TestCase
{
    #[Test]
    public function 默认提供方是jsdelivr(): void
    {
        $resolver = new AssetUrlResolver();

        self::assertSame('jsdelivr', $resolver->provider());
        self::assertFalse($resolver->isLocal());
    }

    #[Test]
    public function jsdelivr模板(): void
    {
        $resolver = new AssetUrlResolver(AssetUrlResolver::JSDELIVR);

        self::assertSame(
            'https://cdn.jsdelivr.net/npm/admin-lte@4.9.1/dist/css/adminlte.min.css',
            $resolver->url('admin-lte', 'dist/css/adminlte.min.css'),
        );
    }

    #[Test]
    public function jsdelivrFastly模板(): void
    {
        $resolver = new AssetUrlResolver(AssetUrlResolver::JSDELIVR_FASTLY);

        self::assertSame(
            'https://fastly.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js',
            $resolver->url('bootstrap', 'dist/js/bootstrap.min.js'),
        );
    }

    #[Test]
    public function unpkg模板(): void
    {
        $resolver = new AssetUrlResolver(AssetUrlResolver::UNPKG);

        self::assertSame(
            'https://unpkg.com/@popperjs/core@2.11.8/dist/umd/popper.min.js',
            $resolver->url('@popperjs/core', 'dist/umd/popper.min.js'),
        );
    }

    #[Test]
    public function local模式不带版本号(): void
    {
        $resolver = new AssetUrlResolver(AssetUrlResolver::LOCAL, '/assets/vendor');

        self::assertTrue($resolver->isLocal());
        self::assertSame(
            '/assets/vendor/admin-lte/dist/css/adminlte.min.css',
            $resolver->url('admin-lte', 'dist/css/adminlte.min.css', '9.9.9'),
        );
    }

    #[Test]
    public function local模式baseUrl末尾斜杠被规整(): void
    {
        $resolver = new AssetUrlResolver(AssetUrlResolver::LOCAL, '/assets/vendor/');

        self::assertSame(
            '/assets/vendor/admin-lte/dist/css/adminlte.min.css',
            $resolver->url('admin-lte', 'dist/css/adminlte.min.css'),
        );
    }

    #[Test]
    public function 路径前导斜杠被剥掉(): void
    {
        $resolver = new AssetUrlResolver();

        self::assertSame(
            'https://cdn.jsdelivr.net/npm/admin-lte@4.9.1/dist/css/adminlte.min.css',
            $resolver->url('admin-lte', '/dist/css/adminlte.min.css'),
        );
    }

    #[Test]
    public function 显式版本号优先于默认表(): void
    {
        $resolver = new AssetUrlResolver();

        self::assertSame(
            'https://cdn.jsdelivr.net/npm/admin-lte@9.9.9/x.css',
            $resolver->url('admin-lte', 'x.css', '9.9.9'),
        );
    }

    #[Test]
    public function 版本号先查配置覆盖再回落默认表(): void
    {
        $resolver = new AssetUrlResolver(versions: ['admin-lte' => '1.2.3']);

        self::assertSame('1.2.3', $resolver->version('admin-lte'));   // 配置覆盖
        self::assertSame('5.3.8', $resolver->version('bootstrap'));    // 回落默认
    }

    #[Test]
    public function 未知包版本号抛异常(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new AssetUrlResolver())->version('no-such-package');
    }

    #[Test]
    public function 未知提供方抛异常(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('未知的资源提供方');

        new AssetUrlResolver('npmmirror');
    }
}
