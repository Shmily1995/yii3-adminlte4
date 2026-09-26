#!/usr/bin/env bash
#
# ============================================================
#  AdminLTE 4 资源本地化脚本
# ============================================================
#
# 【用途】
#   把 AdminLTE 4 及其依赖的**固定版本**下载到指定目录，
#   供 params 里 `'cdn' => 'local'` 模式使用。
#
# 【什么时候需要它】
#   · 生产环境在内网 / 不方便访问外网 CDN
#   · 不想依赖 jsdelivr 的可用性（国内波动较大）
#
# 【用法】
#   bash resources/bin/fetch-assets.sh [目标目录]
#
#   目标目录默认 public/assets/vendor，正对应
#   params 的 adminlte4.assetsBaseUrl 默认值 '/assets/vendor'。
#
# 【产出的目录约定】
#   {目标目录}/{npm 包名}/{包内路径}
#     例：public/assets/vendor/admin-lte/dist/css/adminlte.min.css
#         public/assets/vendor/@popperjs/core/dist/umd/popper.min.js
#
# 【版本来源】
#   与 src/Config.php 的 DEFAULT_VERSIONS 保持一致 ——
#   升级时两处都要改（或设 ADMINLTE4_CDN_BASE 指向你自己的镜像）。
#
set -euo pipefail

TARGET="${1:-public/assets/vendor}"
BASE="${ADMINLTE4_CDN_BASE:-https://cdn.jsdelivr.net/npm}"

# 包名|版本|文件路径（相对包根）
FILES=(
    "admin-lte|4.9.1|dist/css/adminlte.min.css"
    "admin-lte|4.9.1|dist/css/adminlte-colors.css"
    "admin-lte|4.9.1|dist/js/adminlte.min.js"
    "bootstrap|5.3.8|dist/js/bootstrap.min.js"
    "bootstrap-icons|1.13.1|font/bootstrap-icons.min.css"
    "bootstrap-icons|1.13.1|font/fonts/bootstrap-icons.woff"
    "bootstrap-icons|1.13.1|font/fonts/bootstrap-icons.woff2"
    "overlayscrollbars|2.11.0|styles/overlayscrollbars.min.css"
    "overlayscrollbars|2.11.0|browser/overlayscrollbars.browser.es6.min.js"
    "@popperjs/core|2.11.8|dist/umd/popper.min.js"
)

echo "目标目录 : $TARGET"
echo "资源来源 : $BASE"
echo

ok=0
fail=0

for entry in "${FILES[@]}"; do
    IFS='|' read -r pkg ver path <<< "$entry"

    dest="$TARGET/$pkg/$path"
    url="$BASE/$pkg@$ver/$path"

    mkdir -p "$(dirname "$dest")"

    if curl -sSL --fail --retry 3 --retry-delay 2 -o "$dest" "$url"; then
        size=$(wc -c < "$dest" | tr -d ' ')
        printf '  ✓ %-8s %s\n' "$size" "$pkg/$path"
        ok=$((ok + 1))
    else
        printf '  ✗ 下载失败: %s\n' "$url"
        rm -f "$dest"
        fail=$((fail + 1))
    fi
done

echo
echo "完成：成功 $ok 个，失败 $fail 个"

if [ "$fail" -gt 0 ]; then
    echo "有文件下载失败。可尝试用国内镜像重跑："
    echo "  ADMINLTE4_CDN_BASE=https://registry.npmmirror.com bash $0 $TARGET"
    echo "（注意 npmmirror 的路径形态是 /{包}/{版本}/files/{路径}，与本脚本的 @ 形态不同，"
    echo "  如需使用请调整 BASE 拼接方式。）"
    exit 1
fi

echo "接着在应用 params 里设置："
echo "  'adminlte4' => ['cdn' => 'local', 'assetsBaseUrl' => '/assets/vendor']"
