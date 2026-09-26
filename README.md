# yii3-adminlte4

**AdminLTE 4 × Yii3** —— 把 AdminLTE 4 变成 Yii3 里可以 `composer require` 的一等公民。

灵感来自 Yii2 时代的 [`dmstr/yii2-adminlte-asset`](https://github.com/dmstr/yii2-adminlte-asset)：
它把「资源包 + 布局样板 + 数据驱动菜单 + 生成器模板」打包成可复用组件。
本包把这套体验移植到 Yii3，并补上 Yii3 特有的两个缺口（见下方「为什么需要它」）。

- **零 jQuery** —— 与 AdminLTE 4 一致，纯原生 JS / TypeScript 生态
- **零前端构建** —— 不需要 npm / Vite / webpack，服务端渲染直接可用
- **官方标准** —— 资源用官方 `yiisoft/assets` 的 `AssetBundle`，Widget 用官方
  `yiisoft/widget`，遵循 Yii3 官方「包设计规范」，不另起炉灶
- **应用侧零配置** —— 靠 composer 配置插件自动注入 `params` / `di-web` 组

---

## 为什么需要这个包

Yii3 的 `yiisoft/view` 是一次彻底重构，它比 Yii2 **少了两样 AdminLTE 必需的东西**：

| 能力 | Yii2 | Yii3 (yiisoft/view 12.x) | 本包 |
|---|---|---|---|
| 布局（layout）机制 | `$this->layout = 'main'` | **没有**（整个 `src/` 里 "layout" 出现 0 次） | `AdminLteRenderer` 提供两段式渲染 |
| 资源包（AssetBundle） | `yii\web\AssetBundle` + `depends` | **不在 view 包里**（独立为 `yiisoft/assets`） | 6 个资源包，全部 `extends \Yiisoft\Assets\AssetBundle` |

「套布局」这件事在 Yii3 里**必须由应用自己实现**（`yiisoft/view` 没有 layout 概念）。
本包把它收敛到一处；资源包则用官方 `yiisoft/assets`，并提供官方缺失的
「jsdelivr / npmmirror / 自托管」三套 URL 解析。你只需要关心业务页面。

---

## 环境要求

| 项 | 要求 |
|---|---|
| PHP | `^8.1` |
| yiisoft/view | `^12.0` |
| yiisoft/assets | `^5.0` |
| yiisoft/widget | `^2.0` |
| yiisoft/aliases | `^2.0 \|\| ^3.0` |
| psr/container | `^1.1 \|\| ^2.0` |
| 应用的配置插件 | `web` 组需引用 `$di-web`（Yii3 官方骨架默认如此） |

---

## 安装

### 方式一：本地路径（推荐用于同一仓库内的私有包）

在应用 `composer.json` 里加一个 path 仓库：

```json
{
    "require": {
        "interview-treasure/yii3-adminlte4": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "../packages/yii3-adminlte4",
            "options": { "symlink": true }
        }
    ]
}
```

然后：

```bash
composer update interview-treasure/yii3-adminlte4
```

> `symlink: false`（镜像复制）在 Windows 上更稳，但改包内代码后要
> `composer reinstall interview-treasure/yii3-adminlte4` 才会同步到 `vendor/`。
> 开发期建议 `true`。

### 方式二：VCS

```json
{
    "repositories": [
        { "type": "vcs", "url": "git@your-git-host:your-group/yii3-adminlte4.git" }
    ]
}
```

### 装完先确认「真的接上了」

本包不要求你改应用配置，但值得花 10 秒确认配置插件生效了：

```bash
grep -A3 "'interview-treasure/yii3-adminlte4'" config/.merge-plan.php
```

应当能看到它同时出现在 `params` 和 `di-web` 两个组里：

```php
'params' => [
    'interview-treasure/yii3-adminlte4' => ['config/params.php'],
    ...
],
'di-web' => [
    'interview-treasure/yii3-adminlte4' => ['config/di-web.php'],
    ...
],
```

看不到的话，说明 composer 的配置插件没跑 —— 执行 `composer dump-autoload` 再看。

> ⚠️ 本项目的 `config/merge-plan.php`（**不带点**）是历史遗留文件，
> 运行时读的是 `config/.merge-plan.php`（**带点**）。别查错了文件。

---

## 最小接入：3 步就能出一个后台

**第 1 步** 装包：

```bash
composer require interview-treasure/yii3-adminlte4
```

**第 2 步** 写配置（`config/params.php`）—— **站点名和菜单都在这里，不用写 PHP**：

```php
return [
    'adminlte4' => [
        'brand' => '我的后台',
        'menu'  => [
            ['label' => '概览', 'icon' => 'speedometer2', 'url' => '/admin',           'key' => 'dashboard'],
            ['label' => '用户', 'icon' => 'people',       'url' => '/admin/users',     'key' => 'users'],
            ['label' => '题库', 'icon' => 'list-check',   'url' => '/admin/questions', 'key' => 'questions'],
        ],
    ],
];
```

**第 3 步** 在 Action 里渲染一行：

```php
use AdminLte4\AdminLteRenderer;

public function handle(ServerRequestInterface $request): ResponseInterface
{
    $html = $this->renderer->render(
        page: 'admin/users',                     // 你的页面模板
        data: ['rows' => $rows],
        options: ['title' => '用户管理', 'nav' => 'users'],
    );

    return new HtmlResponse($html);
}
```

就这样 —— 侧边栏、顶栏、页脚、面包屑、资源标签全部就位，菜单自动按 `nav` 高亮。
**没有**布局文件要复制、**没有**资源要发布、**没有**菜单类要写。

> 页面模板里如果有裸 `<table>`、裸 `<input>`、`.badge-*` 这类旧写法，
> 包内的**兼容层**会自动把它们补齐成 Bootstrap 5 观感（默认开启，见下文
> [兼容层](#兼容层-compat旧模板迁移不炸样式)）—— 这就是「接入后样式不错乱」的保障。

---

## 快速开始（完整参数版）

```php
use AdminLte4\AdminLteRenderer;
use AdminLte4\Menu\Menu;

/** @var AdminLteRenderer $renderer */
$renderer = $container->get(AdminLteRenderer::class);

$menu = Menu::fromArray([
    ['label' => '概览',     'icon' => 'speedometer2', 'url' => '/admin',            'key' => 'dashboard'],
    ['label' => '内容管理', 'icon' => 'folder2-open', 'children' => [
        ['label' => '题库管理', 'url' => '/admin/questions',  'key' => 'questions',  'badge' => '1284'],
        ['label' => '面经审核', 'url' => '/admin/experiences', 'key' => 'experiences', 'badge' => '17', 'badgeVariant' => 'warning'],
    ]],
    ['label' => '系统', 'header' => true],
    ['label' => '用户管理', 'icon' => 'people', 'url' => '/admin/users', 'key' => 'users'],
]);

$html = $renderer->render(
    page: 'admin/questions',        // 你自己的页面模板（相对应用视图根目录）
    data: ['rows' => $rows],        // 只传给页面模板
    options: [
        'title'       => '题库管理',
        'menu'        => $menu,
        'nav'         => 'questions',                       // 高亮哪个菜单项
        'breadcrumbs' => [['label' => '题库管理']],
        'flash'       => ['level' => 'success', 'message' => '保存成功'],
        'user'        => ['name' => 'admin', 'role' => '超级管理员'],
    ],
);

return new HtmlResponse($html);
```

还没写页面模板？包内自带两个可直接渲染的示例：

```php
$renderer->render(page: AdminLte4\Config::viewsPath() . '/examples/dashboard.php', options: [...]);
$renderer->render(page: AdminLte4\Config::viewsPath() . '/examples/login.php',     options: ['layout' => 'login-layout', ...]);
```

### `render()` 的 `options` 全表

| 键 | 类型 | 说明 |
|---|---|---|
| `title` | string | 页面标题（`<title>` 与页面头部大标题共用） |
| `menu` | `Menu`\|array | 侧边栏菜单；省略时自动取 params 的 `adminlte4.menu` |
| `nav` | string | 要高亮的菜单项 `key` |
| `path` | string | 当前请求路径；仅当没给 `nav` 时用于兜底匹配 |
| `breadcrumbs` | array | `[['label' => 'x', 'url' => '/y'], ...]`（末项自动 active） |
| `flash` | array | `['level' => 'success', 'message' => '...']`（跨请求的一次性提示） |
| `alerts` | array[] | 附加告警条（本请求内产生，如「status 参数不合法」），每条 `['level'=>..,'message'=>..]`，与 `flash` 合并渲染 |
| `user` | array | `['name' => '..', 'role' => '..', 'url' => '..']` |
| `logoutUrl` | string | 链接式登出（**无 CSRF**，仅用于简单场景） |
| `logoutHtml` | string | 自定义登出 HTML（如带 CSRF 的 `<form>`），原样输出 |
| `brand` | string | 站点名 |
| `home` / `homeUrl` | string | 面包屑首页标签 / 地址 |
| `layout` | string | 布局逻辑名（`layout` / `login-layout`），或绝对路径 |
| `bodyClass` | string | 追加到 `<body>` 的类名 |
| `extraAssets` | string[] | **推荐**：应用自己的 `AssetBundle` 类名，在 AdminLTE 主包**之后**注册，可覆盖其同名规则 |
| `extraCss` | string[] | 便捷追加的 CSS URL（不走资源包，仅用于快速打补丁） |
| `compat` | bool | 是否注入旧模板兼容层，默认取 params 的 `adminlte4.compat`（默认 `true`） |

---

## 资源来源：四套模式

后台资源全部经 `AssetUrlResolver` 解析，由 `params` 一个开关切换：

```php
// 应用 config/common/params.php
return [
    'adminlte4' => [
        // jsdelivr | jsdelivr-fastly | unpkg | local
        'cdn' => 'local',
        'assetsBaseUrl' => '/assets/vendor',
    ],
];
```

| 模式 | 产出 URL 形态 | 适用 |
|---|---|---|
| `jsdelivr` | `https://cdn.jsdelivr.net/npm/admin-lte@4.9.1/...` | 默认、演示、海外 |
| `jsdelivr-fastly` | `https://fastly.jsdelivr.net/npm/admin-lte@4.9.1/...` | jsdelivr 慢/不稳时的首选备选 |
| `unpkg` | `https://unpkg.com/admin-lte@4.9.1/...` | 又一个备选源 |
| `local` | `/assets/vendor/admin-lte/dist/css/adminlte.min.css` | **面向国内的生产环境推荐**；内网 / 自托管 |

> **国内踩坑提示**：jsdelivr 在国内时有波动，症状是「页面结构正常、样式全丢、
> 加载卡十几秒」。**生产环境请直接用 `local`**（见下方一键脚本），彻底不依赖外网。

> ⚠️ **曾经的 `npmmirror` 已移除**（2.1.0）。实测（2026-09）三种形态均取不到文件：
> `registry.npmmirror.com/{pkg}/{ver}/files/...` → 403、
> `cdn.npmmirror.com/{pkg}/{ver}/...` → 404、`npm.elemecdn.com/{pkg}@{ver}/...` → 404。
> 保留一个「配了必然 403」的源，只会让人照文档切了源之后样式全丢 —— 故直接删除。

### `local` 模式：一键抓取固定版本

```bash
bash vendor/interview-treasure/yii3-adminlte4/resources/bin/fetch-assets.sh public/assets/vendor
```

脚本会把 AdminLTE 4 及其依赖的**固定版本**文件按
`{目标目录}/{npm 包名}/{包内路径}` 放好，正好是 `local` 模式期望的目录约定。

### 版本升级改哪里

```php
'adminlte4' => [
    'versions' => [
        'admin-lte'         => '4.9.1',
        'bootstrap'         => '5.3.8',
        'bootstrap-icons'   => '1.13.1',
        'overlayscrollbars' => '2.11.0',
        '@popperjs/core'    => '2.11.8',
    ],
],
```

> ⚠️ 别只升 `admin-lte`。AdminLTE 各小版本对 Bootstrap 的 patch 版本有要求，
> 混搭会出现「样式正常但交互组件失灵」这类难查的问题。

## 兼容层 compat（旧模板迁移，不炸样式）

迁移期最常见的情况是：**布局换成了 AdminLTE 4，页面模板还是旧写法** ——
裸 `<table>`、裸 `<input>`/`<select>`、`.badge-*`、把 `.card` 当白底容器用……
这些在 Bootstrap 5 下会掉样式，表现出来就是常说的「**引用后样式错乱**」。

包内置了一份兼容层 CSS，把这些旧写法补齐成 Bootstrap 5 的观感：

```php
'adminlte4' => [
    'compat' => true,     // 默认 true
],
```

也可以按页临时开关：`$renderer->render($page, $data, ['compat' => false])`。

| 它补什么 | 说明 |
|---|---|
| 表单控件 | 裸 `input`/`select`/`textarea` → `.form-control` / `.form-select` 观感（含 focus 描边） |
| 表格 | 裸 `<table>` → 边框、表头底色、行 hover |
| 按钮 | 裸 `.btn`（无颜色变体）→ 描边次要按钮观感，保证可辨识 |
| 徽章 | `.badge-green/red/gray/blue/yellow` → 软色徽章 |
| 卡片 | `.card` 补内边距；`a.card` 给 hover 反馈 |
| 其它 | `.page-title`、`.filter-bar`、`.pager`、`.empty`、`.markdown-body`、登录页 `.field`/`.sub` |

**作用域严格限定**在 `.app-content` 与 `.login-card-body` 内，不会影响侧边栏、
顶栏的官方观感 —— 这是它能安全常开的前提。

**为什么收在包里，而不是各项目自己写一份垫片？**
垫片写在应用里 = 别的 yii3 项目引用本包时没有它，于是「同一个包，在不同项目里
观感不一致」。收进包内后由包的版本统一维护，谁引用都一致。

**实现细节**：它以 CSS 字符串内联进 `<head>`（约 8 KB），而不是发布成一个文件 ——
走文件就要求应用「发布（publish）」包内资源到 web 目录，涉及 basePath/baseUrl/权限，
是接入期最容易卡住的一步。生产若要浏览器缓存，把
`resources/assets/compat.css` 复制到自己站点、关掉 `compat`、再用 `extraCss` 引用即可。

---

### 可选：扩展调色板

v4 把 `.bg-navy` 这类扩展色拆到了独立文件（主表只保留 Bootstrap 主题色）：

```php
'adminlte4' => ['colors' => true],   // 注册 adminlte-colors.css
```

开启后可用 `.text-bg-navy` / `.text-bg-purple` 等 14 色扩展类
（注意形态变化：v3 的 `.bg-navy` → v4 的 `.text-bg-navy`；
`lightblue` 改名 `sky`，`maroon` 改名 `pink`）。

---

## 菜单

菜单有三个来源，**优先级从高到低**：

| 来源 | 用法 | 适用 |
|---|---|---|
| ① `options['menu']` | 每次 render 传 `Menu` 实例或数组 | 菜单要**按当前用户权限动态裁剪** |
| ② params `adminlte4.menu` | 纯配置数组 | **静态菜单 —— 推荐**，零 PHP 代码 |
| ③ 都没有 | 空菜单 | 只要布局不要侧栏的场景 |

② 是「composer require + 写几行配置就能出后台」的关键：应用不必为了渲染菜单
专门写一个适配层。配好之后，Action 里只要传 `nav`，菜单与高亮就自动有了。

### 配置式（推荐）

```php
// 应用 config/params.php
'adminlte4' => [
    'menu' => [
        ['label' => '概览', 'icon' => 'speedometer2', 'url' => '/admin', 'key' => 'dashboard'],
        ['label' => '用户', 'icon' => 'people',       'url' => '/admin/users', 'key' => 'users'],
    ],
],
```

### 数组式

```php
$menu = Menu::fromArray([
    ['label' => '概览', 'icon' => 'speedometer2', 'url' => '/admin', 'key' => 'dashboard'],
    ['label' => '系统', 'header' => true],                       // 分组标题
    ['label' => '题库', 'url' => '/admin/questions', 'key' => 'q',
     'badge' => '1284', 'badgeVariant' => 'danger'],             // 徽标
    ['label' => '文档', 'url' => 'https://x', 'target' => '_blank'],  // 新窗口
    ['label' => '内容', 'children' => [ /* 二级，最多任意层 */ ]],
]);
```

支持的键：`label` `url` `icon` `badge` `badgeVariant` `header` `key`
`target` `children` `linkAttributes`。

### 对象式

```php
use AdminLte4\Menu\MenuItem;

new Menu([
    MenuItem::link('概览', '/admin', 'speedometer2', key: 'dashboard'),
    MenuItem::header('系统'),
]);
```

### 高亮规则

```php
$menu->withActiveItem(key: 'questions');   // ✅ 推荐：确定性最高
$menu->withActiveItem(path: '/admin/questions');  // 规范化后完全相等才命中
```

- 父项若有子项命中，**会自动标记 active** —— 这正是 AdminLTE 展开 treeview 所需的状态，无需手工维护。
- `path` 匹配刻意**不做前缀匹配**：否则 `/admin` 会在 `/admin/questions` 页面里一起亮起来，出现「两项同时高亮」。
- 规范化内容：去掉 query / fragment / 末尾斜杠。所以 `/admin/users?page=2` 能命中声明为 `/admin/users` 的项。

### 自定义渲染

`MenuRenderer` 输出的是 AdminLTE 4 官方 Demo 的标记结构
（`.nav.sidebar-menu` / `.nav-item` / `.nav-link` / `.nav-treeview` / `.nav-header`）
且带 `data-lte-toggle="treeview"`。要换皮肤或改结构，替换
`AdminLte4\Menu\MenuRenderer` 的容器绑定即可，菜单数据结构不用动。

---

## Widget

```php
use AdminLte4\Widget\Card;
use AdminLte4\Widget\Breadcrumbs;
use AdminLte4\Widget\FlashAlerts;
use AdminLte4\Support\Html;
```

```php
// 卡片
echo new Card(
    title: '最近新增题目',
    icon: 'list-task',
    variant: 'primary',
    collapsible: true,
    removable: true,
    body: $tableHtml,          // ⚠️ 原始 HTML，由调用方保证安全
    footer: '<a href="/x">查看全部</a>',
);

// 面包屑
echo new Breadcrumbs([
    ['label' => '概览', 'url' => '/admin'],
    ['label' => '题库管理'],       // 末项自动 active
], homeLabel: '首页');

// 提示
echo FlashAlerts::fromFlash(['level' => 'success', 'message' => '保存成功']);
```

`Html` 辅助：`encode()`（`ENT_QUOTES | ENT_SUBSTITUTE`）、
`attributes()`（支持条件属性）、`classes()`（支持条件类名）、`icon()`。

| Widget | 产出 |
|---|---|
| `Card` | `.card` + header/card-tools/body/footer，工具按钮用 v4 的 `data-lte-toggle="card-*"` |
| `Breadcrumbs` | `<ol class="breadcrumb">`，末项自动 active |
| `FlashAlerts` | 可关闭 alert，用 v4 的 `data-bs-dismiss="alert"` |

---

## 布局

包内提供两个布局，应用不需要复制任何文件：

| 逻辑名 | 文件 | 用途 |
|---|---|---|
| `layout` | `resources/views/layout.php` | 常规后台：顶栏 + 侧边栏 + 内容区 + 页脚 |
| `login-layout` | `resources/views/login-layout.php` | 登录等未认证页：居中卡片，无导航 |

自定义布局有三种方式：

```php
// ① 用应用自己的布局（相对应用视图根目录）
$renderer->render(page: 'admin/q', options: ['layout' => 'admin/my-layout']);

// ② 绝对路径
$renderer->render(page: 'admin/q', options: ['layout' => '/abs/path/layout.php']);

// ③ 复制包内布局到应用里改（改完用 ① 引用）
```

### 布局是怎么被找到的（原理）

Yii3 的 `ViewTrait::resolveViewFilePath()` 对**绝对路径**是放行的
（Windows 看是否含 `:`，Linux 看是否以 `/` 开头），
所以包内模板可以用 `dirname(__DIR__)` 算出来的绝对路径直接交给
`$view->render()` —— 这也是 `yiisoft/error-handler` 的做法。

附带好处：包内模板之间可以用 `./partials/xxx` 互相引用，
`./` 会被解析成「**当前正在渲染的那个文件**所在目录」的相对路径，
因此整棵视图目录树可以随包移动，不会因为安装路径不同而失效。

### 布局可用变量

`$content` `$e` `$title` `$brand` `$menu` `$menuRenderer`
`$breadcrumbWidget` `$flashWidget` `$user` `$logoutUrl` `$logoutHtml` `$bodyClass` `$options`

---

## ⚠️ 常见坑（都是实测踩过的）

### 1. 少了 `beginPage()` / `endPage()`，CSS/JS 一个都不会输出

这是 Yii3 视图资源机制里最隐蔽的一点。

`head()` / `beginBody()` / `endBody()` 在布局里输出的**不是标签**，而是一个占位符：

```
<![CDATA[YII-BLOCK-HEAD-xxxx]]>
```

真正的替换只发生在 **`endPage()`**（`WebView.php:184` 的 `strtr`）——
**`render()` 不做任何替换**。

后果：如果你在布局里写了 `$this->head()` 却没包 `endPage()`，
页面会照常打开、控制台无报错，但**所有 CSS/JS 全部缺失**。
浏览器把那段无效标记当注释忽略掉，表现就是「HTML 结构完整、样式全丢」。

本包的 `AdminLteRenderer::render()` 已经在内部开了一层输出缓冲并调用了
`beginPage()` / `endPage()`，所以**你什么都不用做**。
但如果你绕过渲染器、自己写布局渲染，务必记得这对调用：

```php
ob_start();
$view->beginPage();
echo $view->render($layoutFile, $data);
$view->endPage();
$html = ob_get_clean();
```

### 2. 不要重复引入 `bootstrap.min.css`

AdminLTE 4 的 `adminlte.min.css` **已经内含 Bootstrap 的样式**。
官方 CDN 片段里 CSS 只有三个（icons / overlay-scrollbars / adminlte），
而 `<script>` 才加载 `bootstrap.min.js`。

所以本包的 `Bootstrap5Asset::css()` **故意返回空数组**。
额外引入 bootstrap 的 CSS 会导致「变量覆盖生效但优先级博弈」这类诡异问题。

### 3. v3 → v4 属性/类名改名清单

| v3 | v4 |
|---|---|
| `.wrapper` | `.app-wrapper` |
| `.main-header` | `.app-header` |
| `.main-sidebar` | `.app-sidebar` |
| `.content-wrapper` | `.app-main` |
| `.main-footer` | `.app-footer` |
| `.content-header` | `.app-content-header` |
| `.content` | `.app-content` |
| `data-widget="pushmenu"` | `data-lte-toggle="sidebar"` |
| `data-widget="treeview"` | `data-lte-toggle="treeview"`（加在父项上） |
| `data-widget="card-widget"` | `data-lte-toggle="card-collapse"` / `card-remove` / `card-maximize` |
| `data-widget="fullscreen"` | `data-lte-toggle="fullscreen"` |
| `data-toggle` / `data-dismiss` | `data-bs-toggle` / `data-bs-dismiss` |
| `.dark-mode`（在 body 上） | `data-bs-theme="dark"`（任意层级） |
| `.ml-*` / `.mr-*` | `.ms-*` / `.me-*` |
| `fas fa-home` | `bi bi-house` |
| `data-widget="control-sidebar"` | **已移除** → 用 Bootstrap offcanvas |

写错属性的典型症状：**按钮长得一模一样，但点了没反应**，且控制台不报错。

### 4. `data-bs-theme` 可以作用在任意层级

侧边栏单独变深、其余保持浅色是原生支持的：

```html
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
```

不需要 v3 那种 `sidebar-dark-primary` 皮肤类。

---

## 与 `yiisoft/assets` 的关系（2.0 起：直接依赖）

**2.0 版本起，本包直接依赖官方 `yiisoft/assets`**，不再自研资源层：

- 资源包 `extends \Yiisoft\Assets\AssetBundle`，用 `$css` / `$js` / `$depends` / `$cdn` 声明；
- 依赖树展开、去重由官方 `AssetManager` 负责；
- 输出走官方链路：`register()` → `getCssFiles()` → `$view->addCssFiles()`。

本包额外提供的、官方不管的两件事：

1. **三套 URL 解析**（`AssetUrlResolver`）：jsdelivr / npmmirror / 自托管，
   一个 params 开关切换。官方只管「发布本地文件」，不提供 CDN 提供方切换；
2. **布局与菜单**：`yiisoft/view` 没有 layout 概念，这部分仍由本包补齐。

> ⚠️ 本包**刻意不在** `di-web` 里声明 `AssetManager` / `AssetLoaderInterface` 这两个 DI 键 ——
> 因为 `yiisoft/assets` 自己已经贡献了同名定义，Yii3 禁止同一配置层出现重复键
> （会抛 `Duplicate key ... while building web group`）。
> 本包改为在 `AdminLteRenderer` 的工厂里自建一个带资源包实例的 AssetManager。

---

## 目录结构

```
packages/yii3-adminlte4/
├── composer.json                  # 声明 config-plugin: params + di-web
├── config/
│   ├── params.php                 # 包参数（应用可按递归合并只覆盖某一项）
│   └── di-web.php                 # 服务定义（Web 专属）
├── resources/
│   ├── bin/fetch-assets.sh        # local 模式：抓取固定版本资源
│   └── views/
│       ├── layout.php             # 主布局
│       ├── login-layout.php       # 登录布局
│       ├── partials/              # navbar / sidebar / footer
│       └── examples/              # 可渲染的示例页面（也是测试夹具）
└── src/
    ├── AdminLteRenderer.php       # 主入口：资源 + 页面 + 布局 + 占位符替换
    ├── Config.php                 # 版本表与路径
    ├── Layout.php                 # 布局解析（绝对路径 / 逻辑名）
    ├── Asset/                     # AssetBundle 抽象 / URL 解析 / 注册器 / 6 个具体包
    ├── Menu/                      # MenuItem / Menu / MenuRenderer
    ├── Widget/                    # Widget / Card / Breadcrumbs / FlashAlerts
    └── Support/Html.php           # 转义与属性渲染
```

---

## 从自研后台迁移的建议路径

1. **先并存**：保留现有自研布局，新增一个页面用本包渲染，对比观感。
2. **抽公共壳**：把「顶栏 / 侧边栏的 HTML」换成 `menu` + 布局参数，
   业务页面模板基本不用改（`$e` / `$title` / `$nav` / `$flash` 的命名刻意保持一致）。
3. **逐个页面切换**：一次一个页面，随时可回滚。
4. **最后删旧布局**。

---

## License

MIT。见 [LICENSE](LICENSE)。

本包**不打包也不分发** AdminLTE / Bootstrap 的任何资源文件 ——
它只注册它们的 CDN URL，或指向你自己托管的文件。
AdminLTE 与 Bootstrap 均为 MIT 许可，版权归各自作者所有。
