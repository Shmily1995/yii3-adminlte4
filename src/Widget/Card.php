<?php

declare(strict_types=1);

namespace AdminLte4\Widget;

use AdminLte4\Support\Html;
use Yiisoft\Widget\Widget;

/**
 * ============================================================
 *  卡片 —— AdminLte4\Widget\Card
 * ============================================================
 *
 * 产出 AdminLTE 4 的卡片结构。后台页面里 90% 的内容容器都是它。
 *
 * 【v3 → v4 的注意点】
 *   卡片工具按钮的 data 属性改名了：
 *       v3: data-widget="card-widget"
 *       v4: data-lte-toggle="card-collapse" / "card-remove" / "card-maximize"
 *   官方警告：若用 v3 的写法，按钮会「长得一样但点了没反应」。
 *
 * 【$body 是原始 HTML，不会被转义】
 *   这是刻意设计 —— 卡片内容通常本身就是一段渲染好的 HTML
 *   （比如一个表格）。请**务必**由调用方保证 $body 已转义，
 *   或者只放入由本包/可信来源生成的内容。
 *   反之，$title / $footer 若需要 HTML 请用 withRawTitle()，
 *   默认按纯文本转义。
 */
final class Card extends Widget
{
    public function __construct(
        private readonly string $title = '',
        private readonly string $body = '',
        private readonly string $footer = '',
        private readonly string $icon = '',
        /** 语义色：primary / success / warning / danger / info / secondary，空串为默认灰 */
        private readonly string $variant = '',
        /** true = card-outline 风格（只有描边和标题色，视觉更轻） */
        private readonly bool $outline = true,
        private readonly bool $collapsible = false,
        private readonly bool $removable = false,
        private readonly bool $maximizable = false,
        /** 额外的 card-tools 按钮（已渲染好的 HTML） */
        private readonly string $tools = '',
        /** 追加到最外层 .card 的类名 */
        private readonly string $class = '',
        /** 标题是否按原始 HTML 输出（默认转义） */
        private readonly bool $rawTitle = false,
    ) {
    }

    public function render(): string
    {
        $cardClasses = Html::classes([
            'card',
            $this->variant !== '' ? 'card-' . $this->variant : '',
            $this->variant !== '' && $this->outline ? 'card-outline' : '',
            $this->class,
        ]);

        $html = '<div class="' . Html::encode($cardClasses) . '">';

        if ($this->title !== '' || $this->hasTools()) {
            $html .= $this->renderHeader();
        }

        $html .= '<div class="card-body">' . $this->body . '</div>';

        if ($this->footer !== '') {
            $html .= '<div class="card-footer">' . $this->footer . '</div>';
        }

        return $html . '</div>';
    }

    private function renderHeader(): string
    {
        $html = '<div class="card-header">';

        if ($this->title !== '') {
            // 图标放在 h3 里，用微小的间距类对齐
            $title = $this->icon !== ''
                ? Html::icon($this->icon, 'me-1') . ($this->rawTitle ? $this->title : Html::encode($this->title))
                : ($this->rawTitle ? $this->title : Html::encode($this->title));

            $html .= '<h3 class="card-title">' . $title . '</h3>';
        }

        if ($this->hasTools()) {
            $html .= '<div class="card-tools">' . $this->tools;
            $html .= $this->renderBuiltinTools();
            $html .= '</div>';
        }

        return $html . '</div>';
    }

    private function hasTools(): bool
    {
        return $this->tools !== ''
            || $this->collapsible
            || $this->removable
            || $this->maximizable;
    }

    private function renderBuiltinTools(): string
    {
        $html = '';

        $tools = [
            ['card-collapse', 'dash-lg', '折叠'],
            ['card-maximize', 'arrows-fullscreen', '最大化'],
            ['card-remove', 'x-lg', '关闭'],
        ];

        foreach ($tools as [$toggle, $icon, $label]) {
            $enabled = match ($toggle) {
                'card-collapse' => $this->collapsible,
                'card-maximize' => $this->maximizable,
                'card-remove' => $this->removable,
                default => false,
            };

            if (!$enabled) {
                continue;
            }

            $html .= '<button type="button" class="btn btn-tool"'
                . ' data-lte-toggle="' . $toggle . '"'
                . ' aria-label="' . Html::encode($label) . '">'
                . Html::icon($icon)
                . '</button>';
        }

        return $html;
    }
}
