<?php

/**
 * ============================================================
 *  示例页面：登录表单 —— resources/views/examples/login.php
 * ============================================================
 *
 * 配合 'login-layout' 布局使用（居中卡片、无导航）。
 *
 * ⚠️ 注意这里 {$csrfToken} 的处理方式：
 *    表单**必须**带 CSRF 隐藏域，值由应用注入。
 *    用 $e() 转义 value，避免 token 里的引号破坏属性。
 *
 * 【变量】
 *   $e          callable
 *   $csrfToken  string
 *   $error      string|null
 */

/** @var callable $e */
/** @var string $csrfToken */
/** @var string|null $error */

$error = $error ?? null;
?>

<?php if ($error !== null): ?>
    <p class="login-box-msg text-danger"><?= $e($error) ?></p>
<?php else: ?>
    <p class="login-box-msg">请登录以进入管理后台</p>
<?php endif; ?>

<form method="post" action="">
    <input type="hidden" name="_csrf" value="<?= $e($csrfToken ?? '') ?>">

    <div class="input-group mb-3">
        <input type="text" name="username" class="form-control"
               placeholder="用户名" autocomplete="username" required autofocus>
        <div class="input-group-text"><span class="bi bi-person"></span></div>
    </div>

    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control"
               placeholder="密码" autocomplete="current-password" required>
        <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
    </div>

    <div class="row">
        <div class="col-8">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                <label class="form-check-label" for="remember">记住我</label>
            </div>
        </div>
        <div class="col-4">
            <button type="submit" class="btn btn-primary w-100">登录</button>
        </div>
    </div>
</form>
