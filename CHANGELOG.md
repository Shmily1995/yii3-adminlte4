# Changelog

本包遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

## [2.2.0] - 2026-09-27

把「自托管」从「一次性文档说明」变成**开箱即用的包能力**：包自带全部资源文件，
并接入官方 `yiisoft/yii-view-renderer` 的注入接口。

### 新增

- **包自带 AdminLTE 4 全部资源**（`resources/assets/vendor/`，约 940 KB，10 个文件）
  - `admin-lte` 4.9.1（CSS + colors + JS）、`bootstrap` 5.3.8（仅 JS）、
    `bootstrap-icons` 1.13.1（CSS + woff/woff2 字体）、
    `overlayscrollbars` 2.11.0（CSS + JS）、`@popperjs/core` 2.11.8。
  - 于是 `'cdn' => 'local'` 之后**离线可用**，应用侧不需要跑任何下载/拷贝脚本。
    （此前 `local` 只是个「约定」：要求用户自己用 `fetch-assets.sh` 把文件
    放进 public/，漏做就变成「样式全丢」。）

- **`LocalizableAsset` 抽象基类** —— 把「CDN / 自托管」两条模式收敛到一处。
  - 六个资源包类（`AdminLte4Asset` / `AdminLte4ColorsAsset` / `Bootstrap5Asset` /
    `BootstrapIconsAsset` / `OverlayScrollbarsAsset` / `PopperAsset`）现在只声明三件事：
    `package()` / `cssPaths()` / `jsPaths()`，不再各自写构造逻辑。
  - 自托管分支设 `$cdn = false` + `$sourcePath`（指向包内资源），
    由官方 `AssetPublisher` 发布并回填 `[basePath, baseUrl]`。

- **接入 `yiisoft/yii-view-renderer`**（`^7.0`）
  - 新增 `AdminLte4\Injection\AdminLteInjection`，实现官方
    `CommonParametersInjectionInterface` + `LayoutParametersInjectionInterface`，
    把 `$brand` / `$home` / `$homeUrl` / `$menu` / `$bodyClass` / `$sidebarDark` /
    `$fixedHeader` / `$scrollToTop` 注入视图。
  - 在 `config/di-web.php` 注册；使用 `ViewRenderer` 时 DI 自动生效，
    不需要应用写任何注入代码。

- 新增 params 键 `assetsBasePath`（默认 `@public/assets/vendor`）——
  自托管资源的**发布目标目录**别名。

### 变更

- `resources/bin/fetch-assets.sh` 语义变更：从「应用侧下载工具」变成
  **包作者升级依赖时的同步工具**，默认写入包内 `resources/assets/vendor`。
- 脚本内移除 npmmirror 兜底提示（该源已实测失效），改提示 `unpkg`。

### 说明（红线）

- **`AdminLteInjection` 不处理 CSRF。** 官方 `CsrfViewInjection` 与
  「按 jti 隔离 + 每次渲染重签发」的自研实现语义不同，CSRF 仍由应用中间件负责。
- `yiisoft/yii-view-renderer` 会**连带引入** `yiisoft/csrf` 与
  `yiisoft/data-response`（官方硬依赖）。本包不使用它们，只是依赖树里多出这两个包。

---

## [2.1.0] - 2026-09-27

解决「接入后样式错乱」，并把接入成本降到「装包 + 写几行配置」。

### 新增

- **内置兼容层 `compat`（默认开启）**
  - 新增 `resources/assets/compat.css`，由 `AdminLteRenderer` 以 CSS 字符串
    内联进 `<head>`（约 8 KB），**不需要发布文件、不需要应用放任何东西到 public/**。
  - 补齐旧写法的观感：裸 `<table>`、裸 `input`/`select`/`textarea`、
    无颜色变体的 `.btn`、`.badge-green/red/gray/blue/yellow`、`.card` 内边距、
    `a.card` hover、`.page-title` / `.filter-bar` / `.pager` / `.empty` /
    `.markdown-body`、登录页 `.field` / `.sub`。
  - 作用域严格限定在 `.app-content` 与 `.login-card-body`，不影响官方骨架观感。
  - 开关：params `adminlte4.compat`，或按页 `options['compat']`。
  - **动机**：兼容垫片原本写在某个应用里，导致别的 yii3 项目引用本包时没有它
    —— 这正是「同一个包在不同项目里样式不一致 / 引用后错乱」的根因。收进包内后由版本统一维护。

- **菜单可以由 params 配置**（`adminlte4.menu`）
  - `options['menu']` 现在既接受 `Menu` 实例，也接受数组；
    两者都不传时自动回落到 params 的 `adminlte4.menu`。
  - 于是静态菜单**零 PHP 代码**：配好 params，Action 里只传 `nav` 即可高亮。
  - `home` / `homeUrl`（面包屑首页项）同样支持 params 兜底。

- 新增 CDN 提供方 `jsdelivr-fastly` 与 `unpkg`。

- **补上单元测试**（`tests/Unit/`，39 tests / 69 assertions）
  - `AssetUrlResolver`：四种提供方模板、版本号回落与异常、local 模式、路径规整。
  - `Menu` / `MenuItem`：数组构建、分组/子菜单递归、key/路径高亮、
    无前缀匹配、父项随子项点亮、不可变性。
  - `Html`：**转义引号**（XSS 回归）、`attributes` 条件渲染、`classes`、`icon`。
  - `Config`：默认版本表完整性、包根路径。
  - 跑法：`composer test`（需先 `composer install` 装 dev 依赖）。

### 修复

- **移除已失效的 `npmmirror` 提供方**（⚠️ 常量 `AssetUrlResolver::NPMMIRROR` 已删除）
  - 实测三种 URL 形态均取不到文件：`registry.npmmirror.com/{pkg}/{ver}/files/...` → 403、
    `cdn.npmmirror.com/{pkg}/{ver}/...` → 404、`npm.elemecdn.com/{pkg}@{ver}/...` → 404。
  - 保留它只会让人「照文档切国内源 → 样式全丢」，且页面结构看起来完全正常、极难排查。
  - 迁移：原来用 `npmmirror` 的，请改为 `jsdelivr-fastly`，
    或（生产推荐）用 `local` + `resources/bin/fetch-assets.sh` 自托管。
- URL 生成改为模板表（`TEMPLATES`），新增提供方只需加一行。

### 变更

- **移除 `composer.json` 里的腾讯 Composer 镜像**（此前把第三方下拉保护的 `repositories`
  块写进了本包：`mirrors.cloud.tencent.com` + `{ "packagist.org": false }`）。
  - 公开发布的库不该自带私有镜像，且 packagist.org 被禁用对海外用户不友好。
  - **对使用方零影响**：Composer 官方行为是
    「Repositories are only available to the root package and the repositories
    defined in your dependencies will not be loaded」——
    依赖包里的 `repositories` 本来就不会被加载，删掉只是卫生清理。
  - 需要加速的同学请在**自己的项目**里配全局镜像：
    `composer config -g repos.packagist composer https://mirrors.cloud.tencent.com/composer/`

### 文档

- README 新增「最小接入：3 步就能出一个后台」与「兼容层」章节；
  资源来源章节改写为四套模式并标注 npmmirror 的实测结论。

---

## [2.0.0] - 2026-09-27

**⚠️ 破坏性变更（Breaking Changes）**

本版本把自研的资源层与 Widget 基类**全部换成 Yii3 官方实现**，
包从此「完全按官方约定」组织，任何 Yii3 项目 `composer require` 即可用。

### 变更

- **资源层改为官方 `yiisoft/assets`**
  - `AdminLte4\Asset\AssetBundle`（自研抽象类）**已删除**。
    所有资源包改为 `extends \Yiisoft\Assets\AssetBundle`，
    用 `$css` / `$js` / `$depends` / `$cdn` 这些官方属性声明。
  - `AdminLte4\Asset\AssetRegistrar`（自研注册器）**已删除**，
    依赖树展开与去重由官方 `AssetManager` 负责。
  - `AssetUrlResolver` **保留**：它负责「jsdelivr / npmmirror / 自托管」三套 URL 的生成，
    这部分官方不提供，仍是本包的价值所在。
  - 资源包统一 `$cdn = true`：URL 在构造时已是最终结果，
    不需要 AssetManager 再做「发布到 public/」这一步。

- **Widget 改为官方 `yiisoft/widget`**
  - `AdminLte4\Widget\Widget`（自研基类）**已删除**，`Card` / `Breadcrumbs` /
    `FlashAlerts` 改为 `extends \Yiisoft\Widget\Widget`。
  - 官方基类的 `render(): string` 与 `__toString()` 契约与自研版本一致，
    因此模板里的 `<?= $widget ?>` 写法**无需改动**。

- **渲染链路改为官方写法**
  - `AdminLteRenderer` 改为：
    `$assetManager->register(...)` → `$view->addCssFiles($assetManager->getCssFiles())` → … →
    布局内 `head()` / `beginBody()` / `endBody()`。
  - 新增 `options['extraAssets']`：推荐用它注册应用自己的 AssetBundle 类
    （在 AdminLTE 之后注册，可覆盖其同名规则）。

- **依赖变化**：新增 `yiisoft/assets`、`yiisoft/widget`、`yiisoft/aliases`。

### 迁移指引（从 1.x 升级）

| 1.x | 2.0 |
|---|---|
| `new AdminLte4\Asset\XxxAsset($urls)` + `AssetRegistrar::register()` | `$assetManager->register(XxxAsset::class)` |
| 自研 `AssetBundle::css()`/`js()` 方法 | 官方 `$css` / `$js` 属性 |
| `options['extraCss']` | `options['extraAssets']`（推荐），`extraCss` 仍可用 |

> ⚠️ 若你的应用**自己定义了** `AssetManager` / `AssetLoaderInterface` 的 DI 键，
> 请不要在包外重复声明同名键 —— Yii3 禁止同一配置层出现重复键
> （会抛 `Duplicate key ... while building web group`）。
> 本包已刻意不在 `di-web` 里占用这两个键。

## [1.1.0] - 2026-09-27

### 新增

- `AdminLteRenderer::render()` 的 `options` 新增两个键：
  - `extraCss`（`string[]`）：在 AdminLTE 主包**之后**追加注册的 CSS URL，
    用于应用自己的兼容层 / 定制样式，保证能覆盖 `adminlte.min.css`。
  - `alerts`（`array[]`）：附加告警条（本请求内产生，如「参数不合法」提示），
    与 `flash` 合并交给 `FlashAlerts` 渲染，样式一致、均带可关闭按钮。

## [1.0.0] - 2026-09-27

首个版本。

### 新增

- **资源层**
  - `AssetBundle` 抽象：带 `depends()` 依赖声明与 `jsPosition()`
  - `AssetUrlResolver`：三套来源（`jsdelivr` / `npmmirror` / `local`），
    一个 params 开关切换，URL 差异不再散落在各资源包里
  - `AssetRegistrar`：后序遍历展开依赖树（保证依赖先加载）、按 URL 去重、容忍循环依赖
  - 六个具体资源包：`AdminLte4Asset`、`AdminLte4ColorsAsset`、`Bootstrap5Asset`、
    `BootstrapIconsAsset`、`OverlayScrollbarsAsset`、`PopperAsset`
  - 资源顺序严格对齐 AdminLTE 4 官方 Introduction 的 CDN 片段
    （CSS：icons → overlay-scrollbars → adminlte；JS：popper → bootstrap → overlay-scrollbars → adminlte）

- **菜单层**
  - `MenuItem`（readonly + `with*` 不可变风格）、`Menu`、`MenuRenderer`
  - `Menu::fromArray()` 保留 Yii2 `dmstr/yii2-adminlte-asset` 的数组式声明体验
  - 父项随子项自动展开（`menu-open`）、按 `key` 或规范化 `path` 高亮
  - 分组标题、图标、徽标、二级子菜单、外链 `target`

- **Widget**
  - `Widget` 基类（`render()` + `__toString()`）
  - `Card`、`Breadcrumbs`、`FlashAlerts`
  - `Support\Html`：`encode()`（`ENT_QUOTES | ENT_SUBSTITUTE`）、
    `attributes()`、`classes()`、`icon()`

- **渲染与布局**
  - `AdminLteRenderer`：资源注册 → 页面主体 → 包内布局壳 → 占位符替换，四步封装
  - `Layout`：布局逻辑名与绝对路径解析
  - 包内布局 `layout.php`（常规后台）与 `login-layout.php`（登录等未认证页），
    含 `partials/` 与 `examples/`

- **接入**
  - `composer.json` 声明 `config-plugin`，向 `params` 与 `di-web` 组贡献配置 ——
    应用侧**零改动**即可接入
  - `resources/bin/fetch-assets.sh`：`local` 模式的一键资源本地化

### 已知限制

- 只提供 HTML 层，不含 v3 时代捆绑的第三方 jQuery 组件。
  富交互请自行选型：Select2 → Tom Select、DataTables → Tabulator、
  日期 → Flatpickr、富文本 → Quill、图表 → Chart.js 4。
- AdminLTE 的 calendar / kanban / chat / file-manager 等示例页未移植
  （它们不属于「后台外壳」的职责范围）。
- 尚未提供自动化测试套件（当前验证方式见 README 的冒烟测试说明）。
