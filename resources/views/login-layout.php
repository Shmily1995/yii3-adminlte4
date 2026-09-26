<?php

/**
 * ============================================================
 *  AdminLTE 4 登录页布局 —— resources/views/login-layout.php
 * ============================================================
 *
 * 【为什么不复用主布局】
 *   登录页没有任何「已登录才存在的东西」：没有侧边栏、没有菜单、
 *   没有当前用户、没有面包屑。硬用主布局会带来两个麻烦：
 *     · 所有跟登录态相关的变量都得做成可空，模板里到处是 ?? 判断
 *     · 更糟的是容易漏判 —— 未登录时侧边栏渲染出一个空壳，
 *       看起来像「权限坏了」而不是「这是登录页」
 *   单独一个布局反而更简单，也符合 AdminLTE 官方 login.html 的结构。
 *
 * 【可以拿到的变量】
 *   $content      string   登录表单 HTML
 *   $e            callable HTML 转义
 *   $title        string
 *   $brand        string
 *   $flashWidget  AdminLte4\Widget\FlashAlerts   登录失败提示
 */

use AdminLte4\Support\Html;

/** @var \Yiisoft\View\WebView $this */
/** @var string $content */
/** @var string $title */
/** @var string $brand */
/** @var \AdminLte4\Widget\FlashAlerts $flashWidget */
/** @var string $bodyClass */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title === '' ? $brand : $title . ' · ' . $brand) ?></title>
    <?php $this->head(); ?>
</head>
<body class="<?= $e(Html::classes(['login-page', 'bg-body-secondary', $bodyClass])) ?>">
<?php $this->beginBody(); ?>

<div class="login-box">
    <div class="login-logo">
        <a href="/"><b><?= $e($brand) ?></b></a>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body login-card-body">
            <?= $flashWidget ?>

            <?= $content ?>
        </div>
    </div>
</div>

<?php $this->endBody(); ?>
</body>
</html>
