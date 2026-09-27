<?php

declare(strict_types=1);

namespace AdminLte4\Asset;

/**
 * ============================================================
 *  Bootstrap 5 资源包 —— AdminLte4\Asset\Bootstrap5Asset
 * ============================================================
 *
 * ⚠️ 这里**只引 JS，不引 CSS** —— 是刻意设计，不是遗漏。
 *
 * AdminLTE 4 的 `adminlte.min.css` **已经把 Bootstrap 5 的 CSS 编译进去了**
 * （官方 Introduction 的 CDN 清单里同样没有 bootstrap.min.css）。
 * 若在这里再引一份 bootstrap.min.css，页面会存在两套重复的 Bootstrap 规则，
 * 表现为「改了 Bootstrap 变量不生效 / 样式互相打架」这类极难定位的问题。
 */
final class Bootstrap5Asset extends LocalizableAsset
{
    public array $depends = [
        PopperAsset::class,
    ];

    protected function package(): string
    {
        return 'bootstrap';
    }

    protected function jsPaths(): array
    {
        return ['dist/js/bootstrap.min.js'];
    }
}
