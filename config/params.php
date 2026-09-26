<?php

declare(strict_types=1);

/**
 * ============================================================
 *  AdminLTE 4 集成包 —— 参数组（纯数据）
 * ============================================================
 *
 * 【这些值从哪来、怎么覆盖】
 *   本文件通过 composer.json 的 extra.config-plugin 声明贡献给
 *   **'params' 组**。params 组在 yii-runner 里是**递归合并**的
 *   （RecursiveMerge），所以应用只需在自己配置里写同样的键，
 *   就能只覆盖你关心的那一项，其余保留本包默认值：
 *
 *       // 应用 config/common/params.php
 *       return [
 *           'adminlte4' => [
 *               'cdn' => 'local',       // 只改这一项，versions 等原样保留
 *           ],
 *       ];
 *
 *   ⚠️ 不要在本文件里 new 任何对象。params 组只放「值」——
 *      放了闭包/服务定义会在合并阶段被当成参数值而报错。
 *      「怎么造对象」是 config/di-web.php 的职责。
 */

use AdminLte4\Asset\AssetUrlResolver;
use AdminLte4\Config;

return [
    'adminlte4' => [
        // ------------------------------------------------------------
        //  站点名：出现在 <title> 后缀、侧边栏品牌区、页脚
        // ------------------------------------------------------------
        'brand' => 'Admin Console',

        // ------------------------------------------------------------
        //  侧边栏菜单 —— 「只写配置、不写代码」的关键
        //
        //  在这里配好之后，Action 里只要：
        //      $renderer->render('admin/xxx', $data, ['nav' => 'users'])
        //  菜单与高亮就自动有了，不需要在应用里写任何菜单相关的 PHP。
        //
        //  每项支持：label / icon / url / key / badge / children
        //    · key    用于 nav 匹配高亮（不传则用 url 匹配）
        //    · icon   是 Bootstrap Icons 名，**不含** bi- 前缀
        //    · badge  形如 ['text' => '3', 'class' => 'text-bg-danger']
        //
        //  需要「按当前用户权限裁剪菜单」时，改用每次 render 的
        //  options['menu'] 传 Menu 实例（优先级高于本配置）。
        // ------------------------------------------------------------
        'menu' => [
            // ['label' => '概览', 'icon' => 'speedometer2', 'url' => '/', 'key' => 'dashboard'],
            // ['label' => '用户', 'icon' => 'people', 'url' => '/users', 'key' => 'users'],
        ],

        // 面包屑「首页」项（留空则不显示首页项）
        'home' => '首页',
        'homeUrl' => '/',

        // ------------------------------------------------------------
        //  兼容层：把「自研 / AdminLTE 3 时代写法」补齐成 Bootstrap 5 观感
        //
        //  开启后，页面模板里即使用裸 <table>、裸 <input>、.badge-*、
        //  .card 当白底容器，也能正常显示 —— 这是「接入后样式不错乱」的保障。
        //  作用域严格限定在 .app-content / .login-card-body 内，
        //  不会影响侧边栏、顶栏的官方观感。
        //
        //  全新项目（全部用 Bootstrap 5 标准类名）可设为 false 省几 KB。
        // ------------------------------------------------------------
        'compat' => true,

        // ------------------------------------------------------------
        // 资源来源：jsdelivr | jsdelivr-fastly | unpkg | local
        //   · jsdelivr        官方默认，全球可用
        //   · jsdelivr-fastly jsdelivr 的 fastly 节点，国内通常更快
        //   · unpkg           备选源
        //   · local           完全自托管，走 assetsBaseUrl，不依赖外网
        //
        // ⚠️ 实测（2026-09）：曾经提供的 npmmirror 两种 URL 形态均已失效
        //    （registry.npmmirror.com/{pkg}/{ver}/files/... → 403，
        //     cdn.npmmirror.com/{pkg}/{ver}/... → 404），已从可选值中移除，
        //    避免「照文档切国内源 → 样式全丢」。
        //    面向国内的生产环境请优先用 local（见 resources/bin/fetch-assets.sh）。
        // ------------------------------------------------------------
        'cdn' => AssetUrlResolver::JSDELIVR,

        // cdn = local 时的资源根 URL。
        // 目录约定：{assetsBaseUrl}/{npm 包名}/{文件路径}
        //   例：/assets/vendor/admin-lte/dist/css/adminlte.min.css
        //       /assets/vendor/@popperjs/core/dist/umd/popper.min.js
        // 用 resources/bin/fetch-assets.sh 可一键把固定版本下载到该目录。
        'assetsBaseUrl' => '/assets/vendor',

        // ------------------------------------------------------------
        // 各依赖的版本号（升级时逐个核对，别只升 admin-lte）
        //   ⚠️ AdminLTE 4 的 adminlte.min.css **已内含 Bootstrap 的 CSS**，
        //      所以这里只需要 Bootstrap 的 JS，不需要 bootstrap.min.css。
        //      详见官方 Introduction 的 CDN 片段。
        // ------------------------------------------------------------
        'versions' => Config::DEFAULT_VERSIONS,

        // 是否在页面里额外加载扩展调色板 adminlte-colors.css。
        // 关闭时只用 Bootstrap 主题色；开启后可用 text-bg-navy / bg-purple 等
        // 14 色扩展类（注意 load 顺序必须在 adminlte.css 之后）。
        'colors' => false,

        // 两个内置布局的逻辑名（对应 resources/views/ 下的文件名，不含 .php）。
        // 一般无需改动 —— 想自定义请在应用侧复制布局后再指定绝对路径。
        'layout' => 'layout',
        'loginLayout' => 'login-layout',

        // 观感微调（默认值即 AdminLTE 官方 Demo 的观感）
        'options' => [
            // 侧边栏配色："" | sidebar-dark-* 见 AdminLTE 文档；
            // 这里给的是布尔开关，具体类名由布局模板拼装
            'sidebarDark' => false,
            // 顶栏/侧栏是否固定在视口（fixed 布局）
            'fixedHeader' => true,
            // 是否在页面右下角渲染「回到顶部」
            'scrollToTop' => false,
        ],
    ],
];
