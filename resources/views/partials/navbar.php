<?php

/**
 * ============================================================
 *  顶栏 —— resources/views/partials/navbar.php
 * ============================================================
 *
 * 【变量】
 *   $brand         string        站点名
 *   $user          array|null    ['name' => ..., 'role' => ..., 'url' => ...]
 *   $logoutUrl     string|null   简单的链接式登出（无 CSRF）
 *   $logoutHtml    string        自定义登出 HTML（如带 CSRF 的 <form>），原样输出
 *
 * ⚠️ 关于登出的两种写法：
 *   本项目（面试宝典）的登出是 **POST + CSRF token** 的表单，
 *   不能做成一个 <a href> —— 那会让 CSRF 防护形同虚设。
 *   所以顶部优先使用 $logoutHtml；$logoutUrl 只是给
 *   「登出不需要 CSRF」的简单场景用的便利选项。
 *
 * 【v3 → v4 变化】
 *   侧边栏切换按钮：data-widget="pushmenu" → data-lte-toggle="sidebar"
 *   全屏按钮：      data-widget="fullscreen" → data-lte-toggle="fullscreen"
 *   下拉菜单：      data-toggle="dropdown"   → data-bs-toggle="dropdown"
 */

use AdminLte4\Support\Html;

/** @var \Yiisoft\View\WebView $this */
/** @var string $brand */
/** @var array|null $user */
/** @var string|null $logoutUrl */
/** @var string $logoutHtml */

$userName = is_array($user) ? (string) ($user['name'] ?? '') : '';
$userRole = is_array($user) ? (string) ($user['role'] ?? '') : '';
$userUrl = is_array($user) ? (string) ($user['url'] ?? '') : '';
?>
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="切换侧边栏">
                    <?= Html::icon('list') ?>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="/" class="nav-link">首页</a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="fullscreen" href="#" role="button" aria-label="全屏">
                    <?= Html::icon('arrows-fullscreen') ?>
                </a>
            </li>

            <?php if ($userName !== ''): ?>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-none d-md-inline"><?= Html::encode($userName) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <li class="user-header text-bg-primary">
                            <p>
                                <?= Html::encode($userName) ?>
                                <?php if ($userRole !== ''): ?>
                                    <small><?= Html::encode($userRole) ?></small>
                                <?php endif; ?>
                            </p>
                        </li>

                        <?php if ($userUrl !== ''): ?>
                            <li>
                                <a href="<?= Html::encode($userUrl) ?>" class="dropdown-item">
                                    <?= Html::icon('person', 'me-1') ?>个人资料
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($logoutHtml !== '' || $logoutUrl !== null): ?>
                            <li class="user-footer">
                                <?php if ($logoutHtml !== ''): ?>
                                    <?php /* 带 CSRF 的表单登出，原样输出（由调用方保证来源可信） */ ?>
                                    <?= $logoutHtml ?>
                                <?php else: ?>
                                    <a href="<?= Html::encode((string) $logoutUrl) ?>"
                                       class="btn btn-outline-danger btn-sm float-end">退出登录</a>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
