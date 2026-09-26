<?php

declare(strict_types=1);

namespace AdminLte4\Menu;

/**
 * ============================================================
 *  菜单 —— AdminLte4\Menu\Menu
 * ============================================================
 *
 * 【它做的事，就是参考 dmstr/yii2-adminlte-asset 里那个 items 数组】
 *   Yii2 时代大家习惯这样声明侧边栏：
 *
 *       'items' => [
 *           ['label' => '题库', 'icon' => 'file-code', 'url' => ['/question']],
 *           ['label' => '系统', 'header' => true],
 *           ['label' => '用户', 'icon' => 'users', 'url' => ['/user'],
 *            'badge' => '3'],
 *       ]
 *
 *   本类把这种体验保留下来（Menu::fromArray），同时提供
 *   对象式的构造方式（MenuItem::link(...)）给需要类型安全的地方用。
 *   两者产出的 Menu 完全等价，可以混着用。
 *
 * 【关于高亮】
 *   推荐用 key 匹配：
 *       $menu->withActiveItem(key: $nav)      // $nav 来自 Action
 *   它确定性最高。URL 匹配（传 $path）只做**规范化后的完全相等**，
 *   刻意不做前缀匹配 —— 否则 /admin 会在 /admin/questions 页面里
 *   一起亮起来，出现「两项同时高亮」的错觉。需要前缀语义时请显式传 key。
 */
final class Menu
{
    /** @param array<array-key, MenuItem> $items */
    public function __construct(
        private readonly array $items = [],
    ) {
    }

    /**
     * 从数组定义构建菜单 —— 便于把菜单写在配置文件里。
     *
     * 支持的键：
     *   label(string,必填) url string icon string badge string
     *   badgeVariant string key string target string
     *   header bool（true 时只取 label） children array（递归）
     *   linkAttributes array
     *
     * @param array<array-key, mixed> $items
     */
    public static function fromArray(array $items): self
    {
        $result = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (!empty($item['header'])) {
                $result[] = MenuItem::header((string) ($item['label'] ?? ''));
                continue;
            }

            $result[] = new MenuItem(
                label: (string) ($item['label'] ?? ''),
                url: isset($item['url']) ? (string) $item['url'] : null,
                icon: isset($item['icon']) ? (string) $item['icon'] : null,
                badge: isset($item['badge']) ? (string) $item['badge'] : null,
                badgeVariant: (string) ($item['badgeVariant'] ?? 'primary'),
                key: isset($item['key']) ? (string) $item['key'] : null,
                target: isset($item['target']) ? (string) $item['target'] : null,
                children: self::fromArray((array) ($item['children'] ?? []))->items(),
                linkAttributes: (array) ($item['linkAttributes'] ?? []),
            );
        }

        return new self($result);
    }

    /** @return array<array-key, MenuItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * 返回「已标记高亮」的新菜单（原对象不变）。
     *
     * @param string|null $key  目标菜单项的 key
     * @param string|null $path 当前请求路径；仅当 key 为 null 时用于兜底匹配
     */
    public function withActiveItem(?string $key = null, ?string $path = null): self
    {
        if ($key === null && $path === null) {
            return $this;
        }

        return new self($this->markActive($this->items, $key, $path)['items']);
    }

    /**
     * 递归标记。父项若「有子项命中」也会被标为 active ——
     * 这正是 AdminLTE 展开 treeview 所需的状态。
     *
     * @param array<array-key, MenuItem> $items
     *
     * @return array{items: array<array-key, MenuItem>, matched: bool}
     */
    private function markActive(array $items, ?string $key, ?string $path): array
    {
        $result = [];
        $matchedAny = false;

        foreach ($items as $item) {
            $child = $this->markActive($item->children, $key, $path);

            $isActive = $item->active
                || $child['matched']
                || ($key !== null && $item->key !== null && $item->key === $key)
                || ($key === null && $path !== null && self::urlMatches($item->url, $path));

            $result[] = $item->withActive($isActive)->withChildren($child['items']);
            $matchedAny = $matchedAny || $isActive;
        }

        return ['items' => $result, 'matched' => $matchedAny];
    }

    /**
     * 规范化后完全相等才算命中。
     *
     * 规范化内容：去掉 query / fragment / 末尾斜杠。这样
     * `/admin/users?page=2` 能匹配声明为 `/admin/users` 的菜单项，
     * 但 `/admin/users/edit` **不会**匹配它（避免父项误亮）。
     */
    private static function urlMatches(?string $itemUrl, string $path): bool
    {
        if ($itemUrl === null || $itemUrl === '' || $itemUrl === '#') {
            return false;
        }

        return self::normalize($itemUrl) === self::normalize($path);
    }

    private static function normalize(string $url): string
    {
        $url = strtok($url, '?#') ?: $url;
        $trimmed = rtrim($url, '/');

        return $trimmed === '' ? '/' : $trimmed;
    }
}
