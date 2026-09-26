# Changelog

本包遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

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
