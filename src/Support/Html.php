<?php

declare(strict_types=1);

namespace AdminLte4\Support;

/**
 * ============================================================
 *  HTML 辅助 —— AdminLte4\Support\Html
 * ============================================================
 *
 * 【为什么不直接用 yiisoft/html 的 Html::encode()？】
 *   实测（Yii3 的 yiisoft/html）它内部用的是 **ENT_NOQUOTES** ——
 *   单双引号**不转义**：
 *
 *       Html::encode('" onmouseover="alert(1)')
 *       → '" onmouseover="alert(1)'        ← 引号原样保留
 *
 *   放在文本节点里没事，但一旦进属性：
 *       <a title="<?= Html::encode($userInput) ?>">
 *   攻击者就能闭合属性、注入新属性 —— XSS 成立，
 *   而调用方还以为自己已经转义过了。
 *
 *   本包模板里大量值要进属性（class / title / href / data-*），
 *   所以统一用 **ENT_QUOTES | ENT_SUBSTITUTE**：
 *     · ENT_QUOTES      —— 引号也转义，属性上下文安全
 *     · ENT_SUBSTITUTE  —— 非法 UTF-8 字节替换成 U+FFFD，
 *                          而不是让 htmlspecialchars 返回**空串**
 *                          （那会造成静默的数据丢失）
 *
 *   多转义引号的代价为零，收益是「不用再判断上下文」。
 */
final class Html
{
    /**
     * 转义为 HTML 安全文本。null 视作空串（避免 PHP 8.1+ 的 deprecated 警告）。
     */
    public static function encode(string|int|float|null $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * 把关联数组渲染成 HTML 属性串（含前导空格，可直接插进标签）。
     *
     * 语义约定：
     *   · null / false  → 该属性整体省略（这是「条件属性」的惯用法）
     *   · true          → 渲染成无值属性，如 `disabled`
     *   · 其他          → name="value"，值经 encode() 转义
     *
     * 例：Html::attributes(['class' => 'nav-link', 'disabled' => $isLocked])
     */
    public static function attributes(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $parts[] = $value === true
                ? (string) $name
                : $name . '="' . self::encode(is_scalar($value) ? $value : (string) $value) . '"';
        }

        return $parts === [] ? '' : ' ' . implode(' ', $parts);
    }

    /**
     * 合并 class 列表，过滤空值。
     *
     * 支持两种写法混合，便于按条件拼类名：
     *   Html::classes(['nav-link', 'text-danger' => $hasError, trim($extra)])
     *   → "nav-link text-danger xxx"
     *   数字键 = 无条件加入（空串自动跳过）；字符串键 = 值为真才加入。
     */
    public static function classes(array $classes): string
    {
        $result = [];
        foreach ($classes as $key => $value) {
            if (is_int($key)) {
                if (is_string($value) && $value !== '') {
                    $result[] = $value;
                }
                continue;
            }
            if ($value) {
                $result[] = (string) $key;
            }
        }

        return implode(' ', $result);
    }

    /**
     * 渲染一个 Bootstrap Icons 图标（AdminLTE 4 的默认图标库，SVG 字体）。
     *
     * @param string $name 图标名，不含 `bi-` 前缀，如 'house' / 'journal-text'
     *
     * 用法：Html::icon('house', 'me-2')
     */
    public static function icon(string $name, string $extraClass = ''): string
    {
        return '<i class="' . self::encode(self::classes(['bi', 'bi-' . $name, $extraClass])) . '"></i>';
    }

    private function __construct()
    {
    }
}
