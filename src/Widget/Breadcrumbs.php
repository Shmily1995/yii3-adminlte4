<?php

declare(strict_types=1);

namespace AdminLte4\Widget;

use AdminLte4\Support\Html;
use Yiisoft\Widget\Widget;

/**
 * ============================================================
 *  面包屑 —— AdminLte4\Widget\Breadcrumbs
 * ============================================================
 *
 * 渲染 Bootstrap 5 的 `<ol class="breadcrumb">`。
 * 只负责这一小段 —— 外层 `.app-content-header` 的排版交给布局模板，
 * 这样同一个面包屑既能放在页面头部，也能嵌进卡片里。
 *
 * 【用法】
 *   new Breadcrumbs([
 *       ['label' => '概览', 'url' => '/admin'],
 *       ['label' => '题库'],                    // 最后一项通常不写 url
 *   ])
 *
 * 规则：
 *   · 最后一项强制渲染成 `active`（无需自己判断）
 *   · 有 url 的项才渲染成 <a>，否则是纯文本
 *   · 传 $home 会自动在最前面补一个「首页」项
 */
final class Breadcrumbs extends Widget
{
    /**
     * @param array<array-key, array{label?: string, url?: string|null}> $items
     */
    public function __construct(
        private readonly array $items = [],
        private readonly string $homeLabel = '',
        private readonly string $homeUrl = '/',
        /** 右浮动（AdminLTE 页面头部里的常见排法） */
        private readonly bool $floatEnd = false,
    ) {
    }

    public function render(): string
    {
        $items = $this->items;

        if ($this->homeLabel !== '') {
            array_unshift($items, ['label' => $this->homeLabel, 'url' => $this->homeUrl]);
        }

        if ($items === []) {
            return '';
        }

        $classes = Html::classes(['breadcrumb', 'float-sm-end' => $this->floatEnd]);
        $html = '<ol class="' . Html::encode($classes) . '">';

        $lastIndex = array_key_last($items);

        foreach ($items as $index => $item) {
            $label = (string) ($item['label'] ?? '');
            $url = $item['url'] ?? null;
            $isLast = $index === $lastIndex;

            $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '"'
                . ($isLast ? ' aria-current="page"' : '') . '>';

            if (!$isLast && is_string($url) && $url !== '') {
                $html .= '<a href="' . Html::encode($url) . '">' . Html::encode($label) . '</a>';
            } else {
                $html .= Html::encode($label);
            }

            $html .= '</li>';
        }

        return $html . '</ol>';
    }
}
