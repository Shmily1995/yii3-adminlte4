<?php

/**
 * ============================================================
 *  侧边栏外壳 —— resources/views/partials/sidebar.php
 * ============================================================
 *
 * 只负责「壳」：品牌区 + 滚动容器。
 * 菜单本体由 MenuRenderer 生成（见 AdminLte4\Menu\MenuRenderer），
 * 因为菜单是数据驱动的，不该写死在模板里。
 *
 * 【变量】
 *   $brand         string
 *   $menu          AdminLte4\Menu\Menu              已标记高亮
 *   $menuRenderer  AdminLte4\Menu\MenuRenderer
 *
 * 【v3 → v4 的类名变化】
 *   .main-sidebar   → .app-sidebar
 *   .brand-link     → .sidebar-brand > .brand-link
 *   <ul class="nav sidebar-menu"> 保持不变
 *
 * 【为什么 data-bs-theme="dark" 写在 aside 上】
 *   AdminLTE 4 的深色侧边栏不再是 `sidebar-dark-primary` 这类皮肤类，
 *   而是用 Bootstrap 5.3 原生的主题属性 —— 可以作用在任意元素层级，
 *   所以只让侧边栏变深、其余部分保持浅色是天然支持的。
 *   想换成浅色侧边栏，把 data-bs-theme 与 bg-body-secondary 一起去掉即可。
 */

use AdminLte4\Support\Html;

/** @var \Yiisoft\View\WebView $this */
/** @var string $brand */
/** @var \AdminLte4\Menu\Menu $menu */
/** @var \AdminLte4\Menu\MenuRenderer $menuRenderer */
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="/" class="brand-link">
            <span class="brand-text fw-light"><?= Html::encode($brand) ?></span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="侧边导航">
            <?= $menuRenderer->render($menu) ?>
        </nav>
    </div>
</aside>
