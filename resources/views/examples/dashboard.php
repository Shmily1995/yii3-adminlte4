<?php

/**
 * ============================================================
 *  示例页面：概览 —— resources/views/examples/dashboard.php
 * ============================================================
 *
 * 【这个文件是干什么的】
 *   两个用途：
 *     ① 作为**可复制的起点** —— 新页面照抄这个结构即可；
 *     ② 作为包的冒烟测试夹具（不依赖任何业务代码就能渲染出完整页面）。
 *
 * 【渲染它】
 *   $renderer->render('adminlte4::examples/dashboard', [...]);
 *   或用绝对路径 Config::viewsPath() . '/examples/dashboard.php'。
 *
 * 【可以拿到的变量】
 *   $e           callable  HTML 转义
 *   $stats       array     统计卡片数据（见下面的 ?? 默认值）
 *   $recentRows  array     最近记录
 */

use AdminLte4\Support\Html;
use AdminLte4\Widget\Card;

/** @var callable $e */
/** @var array $stats */
/** @var array $recentRows */

$stats = $stats ?? [
    ['label' => '题目总数', 'value' => 1284, 'icon' => 'journal-text', 'variant' => 'primary'],
    ['label' => '待审面经', 'value' => 17, 'icon' => 'chat-square-text', 'variant' => 'warning'],
    ['label' => '活跃用户', 'value' => 362, 'icon' => 'people', 'variant' => 'success'],
    ['label' => '今日模拟面试', 'value' => 48, 'icon' => 'camera-video', 'variant' => 'info'],
];

$recentRows = $recentRows ?? [
    ['id' => 1, 'title' => 'PHP 的 opcache 是如何提升性能的？', 'category' => 'PHP', 'difficulty' => '中等'],
    ['id' => 2, 'title' => 'MySQL 索引下推（ICP）的适用条件', 'category' => 'MySQL', 'difficulty' => '困难'],
    ['id' => 3, 'title' => 'Redis 持久化 RDB 与 AOF 的取舍', 'category' => 'Redis', 'difficulty' => '中等'],
];
?>

<div class="row">
    <?php foreach ($stats as $stat): ?>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-<?= Html::encode((string) ($stat['variant'] ?? 'primary')) ?>">
                <div class="inner">
                    <h3><?= Html::encode((string) ($stat['value'] ?? 0)) ?></h3>
                    <p><?= Html::encode((string) ($stat['label'] ?? '')) ?></p>
                </div>
                <i class="small-box-icon bi bi-<?= Html::encode((string) ($stat['icon'] ?? 'circle')) ?>"></i>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= new Card(
    title: '最近新增题目',
    icon: 'list-task',
    variant: 'primary',
    collapsible: true,
    body: '<div class="table-responsive">'
        . '<table class="table table-striped align-middle mb-0">'
        . '<thead><tr><th>#</th><th>标题</th><th>分类</th><th>难度</th></tr></thead>'
        . '<tbody>'
        . implode('', array_map(
            static fn (array $row): string => '<tr>'
                . '<td>' . Html::encode((string) $row['id']) . '</td>'
                . '<td>' . Html::encode((string) $row['title']) . '</td>'
                . '<td><span class="badge text-bg-secondary">'
                    . Html::encode((string) $row['category']) . '</span></td>'
                . '<td>' . Html::encode((string) $row['difficulty']) . '</td>'
                . '</tr>',
            $recentRows,
        ))
        . '</tbody></table></div>',
) ?>
