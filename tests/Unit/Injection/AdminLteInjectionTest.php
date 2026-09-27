<?php

declare(strict_types=1);

namespace AdminLte4\Tests\Unit\Injection;

use AdminLte4\Injection\AdminLteInjection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;
use Yiisoft\Yii\View\Renderer\LayoutParametersInjectionInterface;

final class AdminLteInjectionTest extends TestCase
{
    #[Test]
    public function 实现官方注入接口(): void
    {
        $injection = new AdminLteInjection();

        self::assertInstanceOf(CommonParametersInjectionInterface::class, $injection);
        self::assertInstanceOf(LayoutParametersInjectionInterface::class, $injection);
    }

    #[Test]
    public function 公共参数取配置值(): void
    {
        $injection = new AdminLteInjection([
            'brand' => '面试宝典',
            'home' => '概览',
            'homeUrl' => '/admin',
            'menu' => [['label' => '概览', 'url' => '/admin', 'key' => 'dashboard']],
        ]);

        $common = $injection->getCommonParameters();

        self::assertSame('面试宝典', $common['brand']);
        self::assertSame('概览', $common['home']);
        self::assertSame('/admin', $common['homeUrl']);
        self::assertCount(1, $common['menu']);
    }

    #[Test]
    public function 公共参数有默认值(): void
    {
        $common = (new AdminLteInjection())->getCommonParameters();

        self::assertSame('Admin', $common['brand']);
        self::assertSame('/', $common['homeUrl']);
        self::assertSame([], $common['menu']);
    }

    #[Test]
    public function 布局参数取options(): void
    {
        $injection = new AdminLteInjection([
            'options' => [
                'bodyClass' => 'dark-mode',
                'sidebarDark' => true,
                'fixedHeader' => false,
            ],
        ]);

        $layout = $injection->getLayoutParameters();

        self::assertSame('dark-mode', $layout['bodyClass']);
        self::assertTrue($layout['sidebarDark']);
        self::assertFalse($layout['fixedHeader']);
    }
}
