<?php

declare(strict_types=1);

namespace AdminLte4\Tests\Unit\Menu;

use AdminLte4\Menu\Menu;
use AdminLte4\Menu\MenuItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MenuTest extends TestCase
{
    #[Test]
    public function 从数组构建普通菜单项(): void
    {
        $menu = Menu::fromArray([
            ['label' => '概览', 'url' => '/admin', 'icon' => 'house', 'key' => 'dashboard'],
        ]);

        self::assertCount(1, $menu->items());
        $item = $menu->items()[0];
        self::assertSame('概览', $item->label);
        self::assertSame('/admin', $item->url);
        self::assertSame('house', $item->icon);
        self::assertSame('dashboard', $item->key);
        self::assertFalse($item->isHeader());
    }

    #[Test]
    public function 从数组构建分组标题(): void
    {
        $menu = Menu::fromArray([
            ['label' => '系统管理', 'header' => true],
        ]);

        self::assertTrue($menu->items()[0]->isHeader());
    }

    #[Test]
    public function 从数组递归构建子菜单(): void
    {
        $menu = Menu::fromArray([
            [
                'label' => '题目',
                'icon' => 'file-code',
                'key' => 'questions',
                'children' => [
                    ['label' => '列表', 'url' => '/admin/questions', 'key' => 'q-list'],
                    ['label' => '新建', 'url' => '/admin/questions/new', 'key' => 'q-new'],
                ],
            ],
        ]);

        $parent = $menu->items()[0];
        self::assertTrue($parent->hasChildren());
        self::assertCount(2, $parent->children);
        self::assertSame('q-list', $parent->children[0]->key);
    }

    #[Test]
    public function 非数组元素被跳过(): void
    {
        $menu = Menu::fromArray([
            ['label' => 'A', 'url' => '/a'],
            'not-an-array',
            42,
            ['label' => 'B', 'url' => '/b'],
        ]);

        self::assertCount(2, $menu->items());
    }

    #[Test]
    public function 按key高亮(): void
    {
        $menu = Menu::fromArray([
            ['label' => '概览', 'url' => '/admin', 'key' => 'dashboard'],
            ['label' => '用户', 'url' => '/admin/users', 'key' => 'users'],
        ])->withActiveItem(key: 'users');

        self::assertFalse($menu->items()[0]->active);
        self::assertTrue($menu->items()[1]->active);
    }

    #[Test]
    public function 高亮返回新对象原对象不变(): void
    {
        $menu = Menu::fromArray([
            ['label' => '用户', 'url' => '/admin/users', 'key' => 'users'],
        ]);

        $highlighted = $menu->withActiveItem(key: 'users');

        self::assertFalse($menu->items()[0]->active);        // 原对象未变
        self::assertTrue($highlighted->items()[0]->active);
        self::assertNotSame($menu, $highlighted);
    }

    #[Test]
    public function 按路径高亮并剥掉query(): void
    {
        $menu = Menu::fromArray([
            ['label' => '用户', 'url' => '/admin/users', 'key' => 'users'],
        ])->withActiveItem(path: '/admin/users?page=2');

        self::assertTrue($menu->items()[0]->active);
    }

    #[Test]
    public function 按路径高亮并规整末尾斜杠(): void
    {
        $menu = Menu::fromArray([
            ['label' => '概览', 'url' => '/admin/', 'key' => 'dashboard'],
        ])->withActiveItem(path: '/admin');

        self::assertTrue($menu->items()[0]->active);
    }

    #[Test]
    public function 路径匹配不做前缀命中(): void
    {
        $menu = Menu::fromArray([
            ['label' => '概览', 'url' => '/admin', 'key' => 'dashboard'],
            ['label' => '用户', 'url' => '/admin/users', 'key' => 'users'],
        ])->withActiveItem(path: '/admin/users/edit');

        // /admin/users/edit 只该命中 /admin/users，绝不能顺带把 /admin 点亮
        self::assertFalse($menu->items()[0]->active);
        self::assertFalse($menu->items()[1]->active);
    }

    #[Test]
    public function 子项命中时父项也高亮(): void
    {
        $menu = Menu::fromArray([
            [
                'label' => '题目',
                'key' => 'questions',
                'children' => [
                    ['label' => '列表', 'url' => '/admin/questions', 'key' => 'q-list'],
                ],
            ],
        ])->withActiveItem(key: 'q-list');

        self::assertTrue($menu->items()[0]->active);              // 父项点亮（treeview 展开所需）
        self::assertTrue($menu->items()[0]->children[0]->active); // 子项点亮
    }

    #[Test]
    public function 空菜单判定与计数(): void
    {
        $empty = new Menu();
        self::assertTrue($empty->isEmpty());
        self::assertSame(0, $empty->count());

        $one = Menu::fromArray([['label' => 'A', 'url' => '/a']]);
        self::assertFalse($one->isEmpty());
        self::assertSame(1, $one->count());
    }

    #[Test]
    public function 菜单项静态工厂(): void
    {
        $link = MenuItem::link('首页', '/', 'house', 'home');

        self::assertSame('首页', $link->label);
        self::assertSame('home', $link->key);
        self::assertFalse($link->isHeader());
        self::assertFalse($link->hasChildren());

        $header = MenuItem::header('分组');
        self::assertTrue($header->isHeader());
        self::assertNull($header->url);
    }
}
