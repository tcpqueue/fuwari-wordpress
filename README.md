# Fuwari for WordPress

基于 saicaca/fuwari（源码版本 6d39b0dec41282e7852e23e032998a5789abee28）的 WordPress 主题。

[源码仓库](https://github.com/tcpqueue/fuwari-wordpress) · [版本下载](https://github.com/tcpqueue/fuwari-wordpress/releases)

## 使用

将 `fuwari-wp-1.0.1.zip` 上传到 WordPress「外观 → 主题 → 安装主题 → 上传主题」，启用后进入「外观 → Fuwari」。

主题后台提供个人信息、横幅、显示与文章、字体、资源、自定义样式和升级设置。正文使用 WordPress 块编辑器；Fuwari Code 和 Fuwari Note 块用于代码与提示框。GitHub 卡片可以使用 `[fuwari_github repo="saicaca/fuwari"]`，提示框可以使用 `[fuwari_note type="tip" title="标题"]内容[/fuwari_note]`。

界面支持简体中文与英文。访客语言、明暗模式和主题色保存在浏览器中；文章标题与正文保持原语言。

默认中文跟随系统字体，无需下载中文字体：苹果优先苹方，Windows 优先微软雅黑，安卓和 Linux 使用可用的中文无衬线字体。各系统字形可能略有差异。默认英文为 Roboto，代码为 JetBrains Mono。中文也可选择仿宋_GB2312 或自定义字体；这些模式可上传 WOFF2/WOFF 文件并在后台选择。字体文件存放在媒体库中，不随主题升级覆盖；选择系统字体后不加载已上传的中文字体。本站保留的仿宋文件独立存放，不包含在通用主题包内。

## 更新

主题设置保存于 `fuwari_settings`，升级时保留。支持 WordPress 原生 ZIP 升级。默认更新仓库为 `tcpqueue/fuwari-wordpress`，也可配置自己的 WordPress 主题发布仓库或 HTTPS 更新清单，不能直接使用上游 Astro 源码作为更新包。自动更新默认关闭，可在主题后台开启。

1.0.1 将默认中文字体改为系统无衬线字体，并仅为已启用的自定义字体生成加载声明。已有网站升级时仍保留自己的字体选择，可在「字体」页改为「跟随系统字体」。

GitHub Releases 的附件名称为 `fuwari-wp.zip` 或 `fuwari-wp-版本号.zip`，tag 使用 `v1.0.1` 一类的版本号。

自定义清单格式：

```json
{"version":"1.0.1","package":"https://example.com/fuwari-wp-1.0.1.zip","url":"https://example.com/releases/1.0.1","requires":"6.9","requires_php":"8.3","sha256":"64位SHA256校验值"}
```

版本升级前会备份已安装主题到 `wp-content/fuwari-backups/`。Nginx 应禁止外部访问该目录。建议同时保留数据库与上传目录备份。

## 本地资源

CSS、JS、原版字体默认随主题本地加载，SVG 图标内嵌。CDN 使用同一套 assets 目录，可单独配置 CSS、JS 与原版字体。自定义字体保持媒体库地址。GitHub 卡片通过服务器获取并缓存仓库信息，访客浏览器不直接请求 GitHub API。

## 开发

Node.js 22+，pnpm 9.14.4。Python 工具使用 uv、CPython 3.12.14 和项目内 `.venv`。

1. 将本项目放在 Linux 原生目录，执行 `pnpm install --frozen-lockfile`。
2. 执行 `pnpm build:reference`，在 `.cache/fuwari-reference` 准备并构建固定版本的上游源码。
3. 执行 `pnpm build`。如使用已有上游目录，两个构建命令均可通过 `FUWARI_REFERENCE` 指定路径。
4. 执行 `uv sync --locked`，再执行 `uv run python scripts/package.py` 生成安装包、源码包和校验文件。打包前应将源码纳入 Git 管理。

版本号同时维护于 `theme/style.css`、`theme/functions.php` 和 `package.json`。资源使用内容散列或文件修改时间区分版本，避免升级后继续加载旧资源。主题服务器只需 PHP 和 WordPress，不需要 Node.js 常驻。

仓库内附 GitHub Actions 发布流程：推送 `v1.0.1` 一类标签后构建安装 ZIP、源码 ZIP 和 SHA256 校验文件并创建 Release。上游升级需要重新核对组件、字体与资源变化，不会自动把新的 Astro 代码替换进 PHP 主题。

中文仿宋和官方演示横幅为本站独立媒体资源，不包含在通用安装包内。默认主题包使用上游仓库附图，本站横幅使用官方演示图并保留来源署名。

## 本站验收

已在 WordPress 7.1.3、PHP 8.5.10、MySQL 8.4.11 与宝塔 Nginx 环境验证：后台语言及分组选项保存、横幅开关、字体选择和 WOFF2 上传、搜索、手机菜单与归档导航、明暗模式、色相调整、代码高亮与复制、数学公式、图片灯箱、文章目录、ZIP 覆盖升级、备份与回退、设置导入导出。临时测试文章和上传文件已移除。

在线更新从公开仓库的 GitHub Releases 获取 WordPress 安装包。CDN 选项要求镜像完整 assets 目录；当前站点使用本地资源，尚未接入实际 CDN。首页、归档和文章布局按固定版本原版移植，中文字体跟随访客系统；尚未进行所有页面和浏览器的逐像素差分验收。

WP 主题 PHP 代码使用 GPL-2.0-or-later；移植的 Fuwari 部分保留 MIT 许可证和原作者版权声明。Roboto、JetBrains Mono、KaTeX 及第三方前端库保留各自许可证。
