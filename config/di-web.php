<?php

declare(strict_types=1);

/**
 * ============================================================
 *  AdminLTE 4 集成包 —— Web 端 DI 定义组（di-web）
 * ============================================================
 *
 * 【为什么是 'di-web' 而不是 'di'】
 *   AdminLTE 是纯 Web UI，这套服务在控制台进程里毫无意义。
 *   Yii3 的惯例（yiisoft/view 自己也这么做）是把 Web 专属定义
 *   放进 'di-web' 组，控制台就不会白白构建这些对象。
 *
 *   ⚠️ 前提：应用的 configuration.php 里，web 组要引用 '$di-web'。
 *
 * 【本包依赖的官方设施】
 *   yiisoft/assets 的 AssetManager / AssetLoader 是**通用设施**，
 *   理论上应由应用自己定义。但为了让本包做到「composer require 即可用」，
 *   这里提供一份**默认定义**；如果应用已经有自己的 AssetManager 定义，
 *   应用配置会覆盖本文件（应用层的配置优先级更高）。
 *
 * 【$params 从哪来】
 *   Yii3 构建**非 params 组**的文件前，会先注入 $params 变量
 *   （值为 params 组构建完成的数组）。所以这里能直接读
 *   $params['adminlte4']，而应用只要覆盖 params 即可改行为，
 *   完全不必碰本文件。
 */

use AdminLte4\AdminLteRenderer;
use AdminLte4\Asset\AdminLte4Asset;
use AdminLte4\Asset\AdminLte4ColorsAsset;
use AdminLte4\Asset\AssetUrlResolver;
use AdminLte4\Asset\Bootstrap5Asset;
use AdminLte4\Asset\BootstrapIconsAsset;
use AdminLte4\Asset\OverlayScrollbarsAsset;
use AdminLte4\Asset\PopperAsset;
use AdminLte4\Menu\MenuRenderer;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetLoader;
use Yiisoft\Assets\AssetLoaderInterface;
use Yiisoft\Assets\AssetManager;
use Yiisoft\View\WebView;

/** @var array $params */

// 一次性取出本包的参数组；后面的闭包都捕获它。
// 这里用的是「默认值兜底」写法：即使应用完全没配 params，
// 包也能正常工作（用的是包内默认值）。
$adminLte4 = (array) ($params['adminlte4'] ?? []);

return [
    // ------------------------------------------------------------
    //  资源 URL 解析：jsdelivr / jsdelivr-fastly / unpkg / 自托管，由 params 决定
    // ------------------------------------------------------------
    AssetUrlResolver::class => static fn (): AssetUrlResolver => new AssetUrlResolver(
        (string) ($adminLte4['cdn'] ?? AssetUrlResolver::JSDELIVR),
        (string) ($adminLte4['assetsBaseUrl'] ?? '/assets/vendor'),
        (array) ($adminLte4['versions'] ?? []),
    ),

    // ------------------------------------------------------------
    //  页面渲染器（包的主入口）
    // ------------------------------------------------------------
    //  ⚠️⚠️ 为什么在这里 new AssetManager，而不是在 DI 里定义
    //      `AssetManager::class` 让容器注入？
    //
    //      yiisoft/assets 自己会通过 config-plugin 贡献一份
    //      `AssetManager` / `AssetLoaderInterface` 的 DI 定义。
    //      本包如果也定义同名的键，Yii3 会直接抛
    //      "Duplicate key ... while building web group" ——
    //      **同一配置层不允许重复键**。
    //
    //      而我们必须拿到「带资源包实例」的 AssetManager：
    //      官方默认用 `new $className()` 无参构造去建资源包，
    //      那样就拿不到 AssetUrlResolver，
    //      「切换 CDN 提供方 / 自托管」的能力会整个失效。
    //
    //      所以这里自建一个（第 4 参 customizedBundles 传**实例**），
    //      只服务于 AdminLTE 自己的资源，不占用全局的 DI 键。
    //      官方的 Aliases / AssetLoaderInterface 仍然从容器取 ——
    //      它们是通用设施，复用应用已有的那份。
    //
    //  ⚠️ 注入的是**应用自己的 WebView**（容器里已有定义），
    //     而不是包内新建一个 —— 只有这样，页面模板里注册的资源
    //     才能和布局里的 $this->head() 落在同一个视图状态上。
    //     自己 new 一个会得到「样式标签永远为空」的诡异结果。
    AdminLteRenderer::class => static function (
        WebView $view,
        Aliases $aliases,
        AssetLoaderInterface $loader,
        AssetUrlResolver $urls,
        MenuRenderer $menuRenderer,
    ) use ($adminLte4): AdminLteRenderer {
        $assetManager = new AssetManager(
            $aliases,
            $loader,
            [],
            [
                PopperAsset::class => new PopperAsset($urls),
                Bootstrap5Asset::class => new Bootstrap5Asset($urls),
                BootstrapIconsAsset::class => new BootstrapIconsAsset($urls),
                OverlayScrollbarsAsset::class => new OverlayScrollbarsAsset($urls),
                AdminLte4Asset::class => new AdminLte4Asset($urls),
                AdminLte4ColorsAsset::class => new AdminLte4ColorsAsset($urls),
            ],
        );

        return new AdminLteRenderer($view, $assetManager, $menuRenderer, $adminLte4);
    },
];
