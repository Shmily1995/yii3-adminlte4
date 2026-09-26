<?php

declare(strict_types=1);

namespace AdminLte4\Menu;

use AdminLte4\Support\Html;

/**
 * ============================================================
 *  侧边栏菜单渲染器 —— AdminLte4\Menu\MenuRenderer
 * ============================================================
 *
 * 产出 AdminLTE 4 官方 Demo 所用的侧边栏标记：
 *
 *   <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview">
 *     <li class="nav-header">分组标题</li>
 *     <li class="nav-item menu-open">
 *       <a class="nav-link active" href="#" data-lte-toggle="treeview">
 *         <i class="nav-icon bi bi-speedometer"></i>
 *         <p>概览<i class="nav-arrow bi bi-chevron-right"></i></p>
 *       </a>
 *       <ul class="nav nav-treeview">
 *         <li class="nav-item"><a class="nav-link" href="...">...</a></li>
 *       </ul>
 *     </li>
 *   </ul>
 *
 * 【注意 v3 → v4 的两处变化】
 *   · 展开子菜单的属性从 `data-widget="treeview"` 改成
 *     `data-lte-toggle="treeview"`，且加在**父菜单项上**，
 *     由 AdminLTE 的 JS 委托处理（不再需要 jQuery 调用）。
 *   · 图标类名从 `fas fa-*` 改成 `bi bi-*`（Bootstrap Icons）。
 *
 * 【安全】
 *   所有来自菜单定义的文本都过 Html::encode()。
 *   徽标只接受**纯文本**，不接受 HTML —— 参考实现里允许塞 HTML 片段，
 *   但那等于把 XSS 的口子开在配置里，不值得。
 */
final class MenuRenderer
{
    /**
     * 渲染整棵菜单树。
     */
    public function render(Menu $menu, string $extraClass = ''): string
    {
        if ($menu->isEmpty()) {
            return '';
        }

        $classes = Html::classes([
            'nav',
            'sidebar-menu',
            'flex-column',
            $extraClass,
        ]);

        $html = '<ul class="' . Html::encode($classes) . '"'
            . ' data-lte-toggle="treeview" role="navigation" aria-label="主导航">';

        foreach ($menu->items() as $item) {
            $html .= $this->renderItem($item);
        }

        return $html . '</ul>';
    }

    private function renderItem(MenuItem $item): string
    {
        if ($item->isHeader()) {
            return '<li class="nav-header">' . Html::encode($item->label) . '</li>';
        }

        $html = '<li class="' . Html::encode(Html::classes([
            'nav-item',
            // 有子菜单且处于活跃分支 → 默认展开
            'menu-open' => $item->hasChildren() && $item->active,
        ])) . '">';

        $html .= $this->renderLink($item);

        if ($item->hasChildren()) {
            $html .= '<ul class="nav nav-treeview">';
            foreach ($item->children as $child) {
                $html .= $this->renderItem($child);
            }
            $html .= '</ul>';
        }

        return $html . '</li>';
    }

    private function renderLink(MenuItem $item): string
    {
        $attributes = $item->linkAttributes;

        // 有子菜单 → 点击只负责展开/收起，所以强制 href="#"
        // （除非调用方显式给了 url，比如「点进去看总览」的场景）
        $attributes['href'] = $item->url ?? '#';
        $attributes['class'] = Html::classes([
            'nav-link',
            'active' => $item->active,
            $attributes['class'] ?? '',
        ]);

        if ($item->hasChildren()) {
            $attributes['data-lte-toggle'] = 'treeview';
        }
        if ($item->target !== null) {
            $attributes['target'] = $item->target;
        }

        $inner = '';
        if ($item->icon !== null && $item->icon !== '') {
            $inner .= Html::icon($item->icon, 'nav-icon');
        }

        // AdminLTE 4 要求文本包在 <p> 里，箭头才排得对
        $inner .= '<p>';
        $inner .= Html::encode($item->label);

        if ($item->badge !== null && $item->badge !== '') {
            $inner .= '<span class="' . Html::encode(Html::classes([
                'nav-badge',
                'badge',
                'text-bg-' . $item->badgeVariant,
                'me-3',
            ])) . '">' . Html::encode($item->badge) . '</span>';
        }

        if ($item->hasChildren()) {
            $inner .= Html::icon('chevron-right', 'nav-arrow');
        }

        $inner .= '</p>';

        return '<a' . Html::attributes($attributes) . '>' . $inner . '</a>';
    }
}
