<?php

declare(strict_types=1);

namespace AdminLte4;

use InvalidArgumentException;

/**
 * ============================================================
 *  布局解析 —— AdminLte4\Layout
 * ============================================================
 *
 * 【背景：Yii3 的 WebView 没有 layout 概念】
 *   把 vendor/yiisoft/view/src/ 整个 grep 一遍，"layout" 出现 0 次。
 *   WebView 只做一件事：把一个模板文件渲染成字符串。
 *   所以「页面套布局」必须由应用层自己实现 —— 本包把这个实现
 *   收敛到 AdminLteRenderer 一处，并且**布局模板放在包内**，
 *   应用不必复制任何文件过去。
 *
 * 【包内模板是怎么被找到的】
 *   Yii3 的 ViewTrait::resolveViewFilePath() 对「绝对路径」是放行的：
 *     · Windows：路径里含 ':'  → 原样返回
 *     · Linux  ：以 '/' 开头    → 原样返回
 *   于是把 `dirname(__DIR__) . '/resources/views/layout.php'` 交给
 *   $view->render() 就能正常工作，且 Windows / Linux 通吃。
 *   这也是 yiisoft/error-handler 的做法（它用 dirname(__DIR__, 2) . '/templates'）。
 *
 *   附带好处：包内模板之间可以用 `./partials/xxx` 互相引用 ——
 *   `./` 会被解析成「**当前正在渲染的那个文件**所在目录」的相对路径，
 *   因此整个视图目录树可以随包移动，不会因为安装路径不同而失效。
 */
final class Layout
{
    /** 常规后台布局（含顶栏 / 侧边栏 / 页脚） */
    public const MAIN = 'layout';

    /** 登录等未认证页面用的精简布局（居中卡片，无导航） */
    public const LOGIN = 'login-layout';

    /** @return string[] 包内置的布局逻辑名 */
    public static function names(): array
    {
        return [self::MAIN, self::LOGIN];
    }

    /**
     * 包内是否存在该布局。
     */
    public static function exists(string $name): bool
    {
        return $name !== '' && is_file(self::viewsPath() . '/' . $name . '.php');
    }

    /**
     * 解析布局逻辑名为**绝对路径**。不存在时抛异常（早失败优于静默 500）。
     */
    public static function path(string $name): string
    {
        $file = self::viewsPath() . '/' . $name . '.php';

        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf(
                '未找到布局 "%s"。可选：%s。若想用应用自己的布局，请传以 "/" 开头的绝对路径，'
                . '或一个能命中应用视图根目录的相对名（如 "admin/layout"）。',
                $name,
                implode('、', self::names()),
            ));
        }

        return $file;
    }

    /**
     * 判断传入的是不是绝对路径。
     *
     * @see ViewTrait::resolveViewFilePath() 的判定逻辑保持一致
     */
    public static function isAbsolutePath(string $path): bool
    {
        return DIRECTORY_SEPARATOR === '\\'
            ? str_contains($path, ':') || str_starts_with($path, '\\\\')
            : str_starts_with($path, '/');
    }

    public static function viewsPath(): string
    {
        return Config::viewsPath();
    }

    private function __construct()
    {
    }
}
