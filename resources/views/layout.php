<?php

/**
 * ============================================================
 *  AdminLTE 4 主布局 —— packages/yii3-adminlte4/resources/views/layout.php
 * ============================================================
 *
 * 【布局是什么】
 *   外框：<html>/<head> + 顶栏 + 侧边栏 + 内容区 + 页脚，全站共用。
 *   页面模板只负责内容区那一块，渲染结果通过 $content 注入进来。
 *
 * 【这个文件从哪被渲染】
 *   AdminLteRenderer 把它**以绝对路径**交给应用的 WebView
 *   （Yii3 的 WebView 本身没有 layout 概念，README 里有说明）。
 *
 * 【可以拿到的变量】（由 AdminLteRenderer::buildSharedData() 注入）
 *   $content          string                     页面主体 HTML
 *   $e                callable(string):string    HTML 转义
 *   $title            string                     页面标题
 *   $brand            string                     站点名
 *   $menu             AdminLte4\Menu\Menu        侧边栏菜单（已标记高亮）
 *   $menuRenderer     AdminLte4\Menu\MenuRenderer
 *   $breadcrumbWidget AdminLte4\Widget\Breadcrumbs
 *   $flashWidget      AdminLte4\Widget\FlashAlerts
 *   $user             array|null                 当前用户 ['name','role','avatar','url']
 *   $options          array                      来自 params 的观感开关
 *
 * ⚠️ 动态内容一律过 $e()（或 Html::encode）。这是防 XSS 的最后一道防线 ——
 *    用户名、面经标题、分类名都是用户可控内容。
 */

use AdminLte4\Support\Html;

/** @var \Yiisoft\View\WebView $this */
/** @var string $content */
/** @var string $title */
/** @var string $brand */
/** @var \AdminLte4\Menu\Menu $menu */
/** @var \AdminLte4\Menu\MenuRenderer $menuRenderer */
/** @var \AdminLte4\Widget\Breadcrumbs $breadcrumbWidget */
/** @var \AdminLte4\Widget\FlashAlerts $flashWidget */
/** @var array|null $user */
/** @var string|null $logoutUrl */
/** @var string $logoutHtml */
/** @var string $bodyClass */
/** @var array $options */

$fixedHeader = (bool) ($options['fixedHeader'] ?? true);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title === '' ? $brand : $title . ' · ' . $brand) ?></title>
    <?php
    /**
     * head() 输出一个占位符，真正的 <link>/<script> 由
     * AdminLteRenderer 在 endPage() 阶段替换进来。
     * 详见 AdminLteRenderer 类注释 —— 那里解释了为什么不能少了这一步。
     */
    $this->head();
    ?>
</head>
<body class="<?= $e(Html::classes(['layout-fixed' => $fixedHeader, $bodyClass])) ?>">
<?php $this->beginBody(); ?>

<div class="app-wrapper">
    <?= $this->render('./partials/navbar', [
        'brand' => $brand,
        'user' => $user,
        'logoutUrl' => $logoutUrl,
        'logoutHtml' => $logoutHtml,
    ]) ?>

    <?= $this->render('./partials/sidebar', [
        'brand' => $brand,
        'menu' => $menu,
        'menuRenderer' => $menuRenderer,
    ]) ?>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0"><?= $e($title) ?></h3>
                    </div>
                    <div class="col-sm-6">
                        <?= $breadcrumbWidget ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <?php /* 一次性提示放在内容最上方，是后台的通行做法 */ ?>
                <?= $flashWidget ?>

                <?= $content ?>
            </div>
        </div>
    </main>

    <?= $this->render('./partials/footer', ['brand' => $brand]) ?>
</div>

<?php $this->endBody(); ?>
</body>
</html>
