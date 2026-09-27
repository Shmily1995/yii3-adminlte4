#!/usr/bin/env bash
#
# ============================================================
#  AdminLTE 4 资源同步脚本（维护包作者用）
# ============================================================
#
# 【这个脚本现在什么时候用】
#   ⚠️ 本包**已经自带**全部资源（resources/assets/vendor/，约 940 KB），
#      应用侧只需要 `'cdn' => 'local'`，**不需要**跑这个脚本 ——
#      yiisoft/assets 的 AssetPublisher 会在首次渲染时自动发布。
#
#   本脚本的唯一用途是：**包作者要把依赖升级到新版本时**，
#   用它把新版本文件重新抓进包内 resources/assets/vendor/。
#
# 【用法】
#   bash resources/bin/fetch-assets.sh                    # 默认写入包内 resources/assets/vendor
#   bash resources/bin/fetch-assets.sh /tmp/out           # 或写到任意目录做对比检查
#
# 【版本来源】
#   与 src/Config.php 的 DEFAULT_VERSIONS **必须一致**。
#   升级流程：改 Config::DEFAULT_VERSIONS 的版本号 → 跑本脚本 → 提交包。
#
# 【路径形态为什么是 @ 而不是 /files/】
#   早年这里提示过 npmmirror 作为国内兜底，但 2026-09 实测其三种域名
#   （registry.npmmirror.com / cdn.npmmirror.com / npm.elemecdn.com）
#   对 admin-lte 4.9.1 全部返回 403/404，已不可用，故删除该建议。
#   若你需要内网镜像，自行设 ADMINLTE4_CDN_BASE 并保证其路径形态为
#   {BASE}/{包}@{版本}/{文件路径}。
#
set -euo pipefail

# 默认写入**包内**资源目录（与 Config::rootPath() . '/resources/assets/vendor' 对应）
PKG_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TARGET="${1:-$PKG_ROOT/resources/assets/vendor}"
BASE="${ADMINLTE4_CDN_BASE:-https://cdn.jsdelivr.net/npm}"

# 包名|版本|文件路径（相对包根）
# ⚠️ 改这里的同时必须同步 src/Config.php 的 DEFAULT_VERSIONS
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
    echo "有文件下载失败。可换源重试（路径形态须为 {BASE}/{包}@{版本}/{文件路径}）："
    echo "  ADMINLTE4_CDN_BASE=https://unpkg.com bash $0 $TARGET"
    exit 1
fi

echo
echo "完成。若资源有更新，别忘了同步 src/Config.php 的 DEFAULT_VERSIONS 与 CHANGELOG。"
