<?php

declare(strict_types=1);

namespace AdminLte4\Tests\Unit\Support;

use AdminLte4\Support\Html;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Html 是本包模板唯一的转义出口。这里重点锁住两个 XSS 相关的行为：
 * ① encode 必须转义引号（官方 yiisoft/html 用 ENT_NOQUOTES 不转，属性上下文会漏）；
 * ② attributes 必须对值做转义。
 */
final class HtmlTest extends TestCase
{
    #[Test]
    public function 转义双引号(): void
    {
        self::assertSame(
            '&quot; onmouseover=&quot;alert(1)',
            Html::encode('" onmouseover="alert(1)'),
        );
    }

    #[Test]
    public function 转义单引号与尖括号(): void
    {
        self::assertSame(
            '&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;',
            Html::encode("<script>alert('x')</script>"),
        );
    }

    #[Test]
    public function 空值视为空串(): void
    {
        self::assertSame('', Html::encode(null));
        self::assertSame('', Html::encode(''));
    }

    #[Test]
    public function 数字值可转义(): void
    {
        self::assertSame('42', Html::encode(42));
    }

    #[Test]
    public function 属性串省略null与false(): void
    {
        self::assertSame(
            ' class="nav-link"',
            Html::attributes(['class' => 'nav-link', 'title' => null, 'disabled' => false]),
        );
    }

    #[Test]
    public function 属性串true渲染为无值属性(): void
    {
        self::assertSame(' disabled', Html::attributes(['disabled' => true]));
    }

    #[Test]
    public function 属性值被转义(): void
    {
        self::assertSame(
            ' title="&lt;script&gt;"',
            Html::attributes(['title' => '<script>']),
        );
    }

    #[Test]
    public function 空属性数组返回空串(): void
    {
        self::assertSame('', Html::attributes([]));
    }

    #[Test]
    public function 类名列表无条件项与空串跳过(): void
    {
        self::assertSame(
            'nav-link text-danger',
            Html::classes(['nav-link', '', 'text-danger']),
        );
    }

    #[Test]
    public function 类名列表条件项按真假取舍(): void
    {
        self::assertSame(
            'nav-link active',
            Html::classes(['nav-link', 'active' => true, 'hidden' => false]),
        );
    }

    #[Test]
    public function 图标渲染(): void
    {
        self::assertSame(
            '<i class="bi bi-house me-2"></i>',
            Html::icon('house', 'me-2'),
        );
    }

    #[Test]
    public function 图标名被转义(): void
    {
        self::assertSame(
            '<i class="bi bi-&quot;x&quot;"></i>',
            Html::icon('"x"'),
        );
    }
}
