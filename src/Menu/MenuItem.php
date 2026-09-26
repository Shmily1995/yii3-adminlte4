<?php

declare(strict_types=1);

namespace AdminLte4\Menu;

/**
 * ============================================================
 *  菜单项 —— AdminLte4\Menu\MenuItem
 * ============================================================
 *
 * 【为什么用 public readonly + with 方法，而不是 setter】
 *   · readonly 让「菜单构建完就不该再被改」这件事由语言保证，
 *     避免某个页面把共享的菜单对象改坏、影响后续渲染。
 *   · with 方法返回**新实例**而不是就地修改 —— 调用方拿到的是
 *     「加了高亮的那一份」，原对象不受影响。
 *
 *   ⚠️ with 方法里用 `new self(...)` 逐个复制字段，而不是 clone + 赋值：
 *     PHP 8.1 / 8.2 里 readonly 属性**不允许**在 clone 后重新赋值
 *     （8.3 才放开）。本包要求 ^8.1，所以走显式重建这条路。
 */
final class MenuItem
{
    /**
     * @param string                $label          显示文本
     * @param string|null           $url            目标地址；有子菜单时可为 null
     * @param string|null           $icon           Bootstrap Icons 图标名（不含 bi- 前缀）
     * @param string|null           $badge          徽标文本（纯文本，渲染时会被转义）
     * @param string                $badgeVariant   徽标配色（Bootstrap 语义色名）
     * @param bool                  $header         true = 分组标题（不可点击）
     * @param string|null           $key            稳定标识，用于高亮匹配（强烈建议设置）
     * @param bool                  $active         当前是否高亮
     * @param string|null           $target         a 标签的 target，如 '_blank'
     * @param array<array-key,MenuItem> $children   子菜单
     * @param array<string,scalar|null> $linkAttributes 追加到 <a> 上的属性
     */
    public function __construct(
        public readonly string $label = '',
        public readonly ?string $url = null,
        public readonly ?string $icon = null,
        public readonly ?string $badge = null,
        public readonly string $badgeVariant = 'primary',
        public readonly bool $header = false,
        public readonly ?string $key = null,
        public readonly bool $active = false,
        public readonly ?string $target = null,
        public readonly array $children = [],
        public readonly array $linkAttributes = [],
    ) {
    }

    /**
     * 一个可点击的菜单项。
     *
     * @param array<array-key,MenuItem> $children
     */
    public static function link(
        string $label,
        ?string $url = null,
        ?string $icon = null,
        ?string $key = null,
        array $children = [],
        ?string $badge = null,
        string $badgeVariant = 'primary',
        ?string $target = null,
    ): self {
        return new self(
            label: $label,
            url: $url,
            icon: $icon,
            badge: $badge,
            badgeVariant: $badgeVariant,
            key: $key,
            target: $target,
            children: $children,
        );
    }

    /**
     * 一个不可点击的分组标题（AdminLTE 里渲染成 <li class="nav-header">）。
     */
    public static function header(string $label): self
    {
        return new self(label: $label, header: true);
    }

    public function isHeader(): bool
    {
        return $this->header;
    }

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }

    public function withActive(bool $active): self
    {
        return new self(
            label: $this->label,
            url: $this->url,
            icon: $this->icon,
            badge: $this->badge,
            badgeVariant: $this->badgeVariant,
            header: $this->header,
            key: $this->key,
            active: $active,
            target: $this->target,
            children: $this->children,
            linkAttributes: $this->linkAttributes,
        );
    }

    /**
     * @param array<array-key,MenuItem> $children
     */
    public function withChildren(array $children): self
    {
        return new self(
            label: $this->label,
            url: $this->url,
            icon: $this->icon,
            badge: $this->badge,
            badgeVariant: $this->badgeVariant,
            header: $this->header,
            key: $this->key,
            active: $this->active,
            target: $this->target,
            children: $children,
            linkAttributes: $this->linkAttributes,
        );
    }
}
