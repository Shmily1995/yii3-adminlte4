<?php

declare(strict_types=1);

namespace AdminLte4\Widget;

use AdminLte4\Support\Html;
use Yiisoft\Widget\Widget;

/**
 * ============================================================
 *  一次性提示 —— AdminLte4\Widget\FlashAlerts
 * ============================================================
 *
 * 把「提示消息」渲染成 Bootstrap 5 的可关闭 alert。
 *
 * 【v3 → v4 迁移注意：关闭按钮的 data 属性变了】
 *   v3: data-dismiss="alert"        （Bootstrap 4 风格）
 *   v4: data-bs-dismiss="alert"     （Bootstrap 5 加了 bs- 前缀）
 *   写错的话提示框**点 X 关不掉**，且控制台不报错 —— 很难查。
 *
 * 【输入格式】
 *   FlashAlerts::fromFlash($flash)          // ['level' => 'success', 'message' => '...']
 *   new FlashAlerts([['level' => 'danger', 'message' => '...'], ...])   // 多条
 *
 *   level 直接映射到 Bootstrap 语义色（alert-success 等）。
 *   未知 level 一律降级成 'info'，不抛异常 —— 提示消息不该让页面挂掉。
 */
final class FlashAlerts extends Widget
{
    private const ALLOWED_LEVELS = ['success', 'danger', 'warning', 'info', 'primary', 'secondary'];

    /** @param array<array-key, array{level?: string, message?: string}> $alerts */
    public function __construct(
        private readonly array $alerts = [],
        /** 标题（可选）：会在消息上方加一行粗体 */
        private readonly string $heading = '',
    ) {
    }

    /**
     * 从应用层的 flash 结构构建（常见形态：单条 ['level'=>..,'message'=>..]）。
     *
     * @param array{level?: string, message?: string}|null $flash
     */
    public static function fromFlash(?array $flash, string $heading = ''): self
    {
        return new self($flash === null ? [] : [$flash], $heading);
    }

    public function render(): string
    {
        $html = '';

        foreach ($this->alerts as $alert) {
            $message = (string) ($alert['message'] ?? '');
            if ($message === '') {
                continue;
            }

            $level = (string) ($alert['level'] ?? 'info');
            if (!in_array($level, self::ALLOWED_LEVELS, true)) {
                $level = 'info';
            }

            $html .= $this->renderAlert($level, $message);
        }

        return $html;
    }

    private function renderAlert(string $level, string $message): string
    {
        $html = '<div class="alert alert-' . Html::encode($level)
            . ' alert-dismissible fade show" role="alert">';

        if ($this->heading !== '') {
            $html .= '<strong>' . Html::encode($this->heading) . '</strong> ';
        }

        // 提示文本按纯文本转义 —— 它常含用户输入（如「面经《xxx》已通过」）
        $html .= Html::encode($message);

        $html .= '<button type="button" class="btn-close"'
            . ' data-bs-dismiss="alert" aria-label="关闭"></button>';

        return $html . '</div>';
    }
}
