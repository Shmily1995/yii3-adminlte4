<?php

/**
 * ============================================================
 *  页脚 —— resources/views/partials/footer.php
 * ============================================================
 *
 * 【变量】
 *   $brand  string
 *
 * 【v3 → v4 类名变化】 .main-footer → .app-footer
 *
 * 刻意保持极简：后台页脚放太多东西只会分散注意力，
 * 而且打印时它和顶栏、侧边栏一样会被自动排除
 * （AdminLTE 4 的打印样式会把内容区铺满整张纸）。
 */

use AdminLte4\Support\Html;

/** @var string $brand */
?>
<footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
        Powered by <a href="https://adminlte.io" rel="noopener noreferrer" target="_blank">AdminLTE 4</a>
    </div>
    <strong><?= Html::encode($brand) ?></strong>
</footer>
