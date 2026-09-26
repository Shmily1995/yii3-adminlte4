<?php

declare(strict_types=1);

namespace AdminLte4;

use AdminLte4\Asset\AdminLte4Asset;
use AdminLte4\Asset\AdminLte4ColorsAsset;
use AdminLte4\Menu\Menu;
use AdminLte4\Menu\MenuRenderer;
use AdminLte4\Support\Html;
use AdminLte4\Widget\Breadcrumbs;
use AdminLte4\Widget\FlashAlerts;
use Throwable;
use Yiisoft\Assets\AssetManager;
use Yiisoft\View\WebView;

/**
 * ============================================================
 *  页面渲染器 —— AdminLte4\AdminLteRenderer（本包的主入口）
 * ============================================================
 *
 * 【用法】
 *   $renderer = $container->get(AdminLteRenderer::class);
 *
 *   return new HtmlResponse($renderer->render(
 *       page: 'admin/questions',                  // 应用自己的页面模板
 *       data: ['rows' => $rows],
 *       options: [
 *           'title'       => '题库管理',
 *           'menu'        => $menu,               // AdminLte4\Menu\Menu
 *           'nav'         => 'questions',         // 高亮哪个菜单项
 *           'breadcrumbs' => [['label' => '题库']],
 *           'flash'       => $flash,              // ['level'=>..,'message'=>..]
 *           'user'        => ['name' => 'admin', 'role' => '超级管理员'],
 *       ],
 *   ));
 *
 * 【它做四件事，顺序不能变】
 *   ① 用官方 AssetManager 注册 AdminLTE 4 及其依赖（递归展开 $depends）
 *   ② 把 AssetManager 收集到的 CSS/JS 交给**应用自己的 WebView**
 *   ③ 渲染页面主体 —— 用应用的视图根目录解析页面模板
 *   ④ 渲染包内布局壳，并把占位符替换成真实资源标签
 *
 * 【⚠️ 资源链路为什么必须走 addCssFiles / addJsFiles】
 *   `AssetManager::register()` 只做一件事：**收集**。
 *   它既不检查文件、也不产出任何 HTML ——
 *   想把资源真正写进页面，必须自己把收集结果转交给视图：
 *
 *       $assetManager->register(AdminLte4Asset::class);
 *       $view->addCssFiles($assetManager->getCssFiles());
 *       $view->addJsFiles($assetManager->getJsFiles());
 *
 *   少掉后半段的症状是：页面正常打开、控制台无报错，但资源一个都不输出。
 *
 * 【⚠️⚠️ 最关键的一处：为什么要自己包 beginPage / endPage】
 *   yiisoft/view 的资源注入不是「addCssFiles 后立刻变成标签」，
 *   而是分成两步：
 *     · head()/beginBody()/endBody() 在布局里输出一个**占位符**
 *       `<![CDATA[YII-BLOCK-HEAD-xxxx]]>`
 *     · **endPage() 才**用 strtr 把占位符替换成真正的 <link>/<script>
 *   我把 vendor/yiisoft/view 的源码翻过：**render() 不做替换**，
 *   整个替换逻辑只在 endPage()（WebView.php:184 附近）。
 *
 *   后果：如果直接在布局里写 `$this->head()` 而不包 endPage，
 *   页面会照常打开、控制台无报错，但**所有 CSS/JS 一个都不输出** ——
 *   浏览器把它当无效标记忽略掉。表现就是「HTML 结构完整、样式全丢」，
 *   极难定位。
 *
 *   所以本方法自己开一层输出缓冲，把「渲染 + 替换」封装在内部，
 *   对外仍然只返回字符串。调用方不需要知道 beginPage/endPage 的存在，
 *   也就无从漏掉。
 */
final class AdminLteRenderer
{
    /**
     * 兼容层 CSS 在视图里的注册键。固定字符串，保证「同请求多次渲染只输出一份」。
     */
    private const COMPAT_CSS_KEY = 'adminlte4/compat';

    /**
     * 兼容层 CSS 内容的进程内缓存。
     */
    private static ?string $compatCssCache = null;

    /**
     * @param array<string,mixed> $config params 里的 'adminlte4' 参数组
     */
    public function __construct(
        private readonly WebView $view,
        private readonly AssetManager $assetManager,
        private readonly MenuRenderer $menuRenderer,
        private readonly array $config = [],
    ) {
    }

    /**
     * 渲染一个完整页面。
     *
     * @param string               $page    页面模板。相对应用的视图根目录（如 'admin/questions'），
     *                                      或以 / 开头的绝对路径
     * @param array<string,mixed>  $data    传给**页面模板**的数据（布局拿不到，避免变量串味）
     * @param array<string,mixed>  $options 页面级选项。除类注释里列出的键外，另支持：
     *                                        - 'extraAssets' string[] **推荐**：应用自己的
     *                                          AssetBundle 类名，在 AdminLTE 之后注册
     *                                        - 'extraCss'    string[] 便捷追加的 CSS URL
     *                                          （非官方链路，仅用于快速打补丁）
     *                                        - 'alerts'      array[]  附加告警条（如参数非法提示），
     *                                                              每条形如 ['level'=>..,'message'=>..]
     */
    public function render(string $page, array $data = [], array $options = []): string
    {
        $this->registerAssets($options);

        $shared = $this->buildSharedData($options);

        // ---------- ① 页面主体 ----------
        // 页面模板可以覆盖共享变量（array_merge 后者优先）
        $pageHtml = $this->view->render($page, array_merge($shared, $data));

        // ---------- ② 布局壳 ----------
        // 只把「共享变量 + content」交给布局。
        // 这是刻意的：页面专有变量（如 $rows）不进布局，
        // 免得布局哪天手滑用了 $rows，在别的页面就变成 Undefined variable。
        $layout = (string) ($options['layout'] ?? $this->config['layout'] ?? Layout::MAIN);
        $layoutFile = $layout !== '' && Layout::exists($layout) ? Layout::path($layout) : $layout;

        $layoutHtml = $this->view->render(
            $layoutFile,
            array_merge($shared, ['content' => $pageHtml]),
        );

        // ---------- ③ 把占位符换成真实资源标签 ----------
        return $this->renderWithPlaceholders($layoutHtml);
    }

    /**
     * 用官方 AssetManager 注册资源，并把结果转交给 WebView。
     *
     * 【为什么判断 isRegisteredBundle】
     *   AssetManager 是应用容器里的单例，而一次请求里可能渲染多次
     *   （例如异常页 fallback 又渲染了一次）。重复 register 虽然
     *   被内部去重，但会白白再走一遍依赖树解析，这里直接跳过。
     *
     * @param array<string,mixed> $options
     */
    private function registerAssets(array $options): void
    {
        if (!$this->assetManager->isRegisteredBundle(AdminLte4Asset::class)) {
            $this->assetManager->register(AdminLte4Asset::class);
        }

        if (!empty($this->config['colors'])
            && !$this->assetManager->isRegisteredBundle(AdminLte4ColorsAsset::class)
        ) {
            $this->assetManager->register(AdminLte4ColorsAsset::class);
        }

        // 应用自己的资源包（推荐做法）：在 AdminLTE 之后注册，
        // 这样它们的规则能覆盖 adminlte.min.css 的同名规则。
        foreach ((array) ($options['extraAssets'] ?? []) as $bundleClass) {
            if (is_string($bundleClass)
                && $bundleClass !== ''
                && !$this->assetManager->isRegisteredBundle($bundleClass)
            ) {
                $this->assetManager->register($bundleClass);
            }
        }

        // ---------- 官方链路：收集结果 → 视图 ----------
        $this->view
            ->addCssFiles($this->assetManager->getCssFiles())
            ->addCssStrings($this->assetManager->getCssStrings())
            ->addJsFiles($this->assetManager->getJsFiles())
            ->addJsStrings($this->assetManager->getJsStrings())
            ->addJsVars($this->assetManager->getJsVars());

        // 便捷补丁：直接给一个 CSS URL（不走 bundle）。
        // 正式项目建议改用 extraAssets 定义自己的 AssetBundle。
        foreach ((array) ($options['extraCss'] ?? []) as $url) {
            if (is_string($url) && $url !== '') {
                $this->view->registerCssFile($url);
            }
        }

        // ---------- 兼容层（旧模板迁移垫片）----------
        $this->registerCompat($options);
    }

    /**
     * 注入包内兼容层 CSS。
     *
     * 【为什么它是包的一部分】
     *   「页面模板用的是自研/AdminLTE 3 时代写法」是迁移期的普遍状态：
     *   裸 <table>、裸 <input>、.badge-*、.card 当白底容器……
     *   这些在 Bootstrap 5 下会掉样式，表现为「骨架换了、内容区还是旧的」。
     *
     *   如果这份垫片写在某个应用里，别的 yii3 项目引用本包就**没有它**，
     *   于是「同一个包，在不同项目里观感不一致」——正是「引用后样式错乱」的来源。
     *   收进包内后由包的版本统一维护，谁引用都一致。
     *
     * 【为什么用 CSS 字符串内联，而不是发布一个文件】
     *   走文件就要求应用把包内资源「发布（publish）」到 web 可访问目录，
     *   涉及 basePath/baseUrl/目录权限，是接入阶段最容易卡住的一步。
     *   本层只有几 KB，内联进 <head> 可换来「零配置可用」。
     *   （生产若要缓存，可把内容复制到自己站点并用 extraCss 引用。）
     *
     * 【开关】params 的 adminlte4.compat（默认 true）。
     *   全新项目全部用 Bootstrap 5 类名时，可关掉省几 KB。
     */
    private function registerCompat(array $options): void
    {
        $enabled = (bool) ($options['compat'] ?? $this->config['compat'] ?? true);

        if (!$enabled) {
            return;
        }

        $css = $this->compatCss();

        if ($css !== '') {
            // key 用固定字符串：同一次请求里渲染多次也只输出一份
            $this->view->addCssStrings([self::COMPAT_CSS_KEY => $css]);
        }
    }

    /**
     * 读取包内兼容层 CSS（进程内缓存，避免重复 IO）。
     */
    private function compatCss(): string
    {
        if (self::$compatCssCache !== null) {
            return self::$compatCssCache;
        }

        $file = dirname(__DIR__) . '/resources/assets/compat.css';
        $css = is_file($file) ? (string) file_get_contents($file) : '';

        return self::$compatCssCache = $css;
    }

    /**
     * 页面模板与布局共用的变量。
     *
     * 命名上刻意贴近本项目既有的 AdminView 约定（$e / $title / $nav / $flash ...），
     * 这样从自研布局迁移过来时，模板里的写法基本不用改。
     *
     * @param array<string,mixed> $options
     *
     * @return array<string,mixed>
     */
    private function buildSharedData(array $options): array
    {
        $title = (string) ($options['title'] ?? '');
        $nav = isset($options['nav']) ? (string) $options['nav'] : null;

        // 菜单可以来自三处，优先级从高到低：
        //   ① 每次 render 的 options['menu']（Menu 实例或数组）—— 动态菜单（按权限裁剪）用这个
        //   ② params 的 adminlte4.menu（数组）—— 静态菜单「只写配置不写代码」用这个
        //   ③ 都没有 → 空菜单
        // ②的存在正是「composer require + 写几行 params 就能出后台」的关键：
        //   应用不必为了渲染菜单而专门写一个 PHP 适配层。
        $menu = $this->resolveMenu($options['menu'] ?? $this->config['menu'] ?? null, $nav, $options);

        $breadcrumbs = (array) ($options['breadcrumbs'] ?? []);
        $flash = $options['flash'] ?? null;

        // 一次性提示（$flash，跨请求）+ 附加告警条（$options['alerts']，
        // 本请求内产生的提示，如「status 参数不合法」）统一交给
        // FlashAlerts 渲染，保证两者样式一致、都带可关闭按钮。
        $alerts = [];
        if (is_array($flash)) {
            $alerts[] = $flash;
        }
        foreach ((array) ($options['alerts'] ?? []) as $extra) {
            if (is_array($extra)) {
                $alerts[] = $extra;
            }
        }

        return [
            // 转义函数：模板里一律用 $e($userInput)，不要用 htmlspecialchars
            'e' => Html::encode(...),
            'title' => $title,
            'brand' => (string) ($options['brand'] ?? $this->config['brand'] ?? 'Admin'),
            'menu' => $menu,
            'menuRenderer' => $this->menuRenderer,
            'nav' => $nav ?? '',
            'breadcrumbs' => $breadcrumbs,
            'breadcrumbWidget' => new Breadcrumbs(
                $breadcrumbs,
                homeLabel: (string) ($options['home'] ?? $this->config['home'] ?? ''),
                homeUrl: (string) ($options['homeUrl'] ?? $this->config['homeUrl'] ?? '/'),
                floatEnd: true,
            ),
            'flash' => $flash,
            'flashWidget' => new FlashAlerts($alerts),
            'user' => $options['user'] ?? null,
            'logoutUrl' => $options['logoutUrl'] ?? null,
            // 需要 CSRF 表单登出时用这个（原样输出，由调用方保证安全）
            'logoutHtml' => (string) ($options['logoutHtml'] ?? ''),
            'bodyClass' => (string) ($options['bodyClass'] ?? ''),
            'options' => (array) ($this->config['options'] ?? []),
        ];
    }

    /**
     * 把「Menu 实例 / 菜单数组 / null」统一解析成已标记高亮的 Menu。
     *
     * @param mixed                $menuOption Menu 实例、菜单数组或 null
     * @param string|null          $nav        当前高亮的菜单 key
     * @param array<string,mixed>  $options
     */
    private function resolveMenu(mixed $menuOption, ?string $nav, array $options): Menu
    {
        if ($menuOption instanceof Menu) {
            return $menuOption->withActiveItem(
                $nav,
                isset($options['path']) ? (string) $options['path'] : null,
            );
        }

        if (is_array($menuOption) && $menuOption !== []) {
            return Menu::fromArray($menuOption)->withActiveItem(
                $nav,
                isset($options['path']) ? (string) $options['path'] : null,
            );
        }

        return new Menu();
    }

    /**
     * 完成「占位符 → 资源标签」的替换，并返回最终 HTML。
     *
     * 实现要点见类注释。这里额外做了两件防御：
     *   · 成功路径用 ob_get_clean() 取走缓冲，绝不残留
     *   · 异常路径把缓冲削回进入前的层级，避免「半截页面 + 后续内容错位」
     */
    private function renderWithPlaceholders(string $html): string
    {
        $level = ob_get_level();

        ob_start();

        try {
            $this->view->beginPage();
            echo $html;
            $this->view->endPage();

            $result = ob_get_clean();

            return $result === false ? '' : $result;
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
    }
}
