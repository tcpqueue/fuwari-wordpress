# Fuwari for WordPress

基于 [saicaca/fuwari](https://github.com/saicaca/fuwari) 的 WordPress 主题，上游源码固定于 `6d39b0dec41282e7852e23e032998a5789abee28`。

[源码仓库](https://github.com/tcpqueue/fuwari-wordpress) · [版本下载](https://github.com/tcpqueue/fuwari-wordpress/releases)

## 安装和设置

将 `fuwari-wp-1.1.0.zip` 上传到 WordPress「外观 → 主题 → 安装主题 → 上传主题」，启用后进入「外观 → Fuwari」。支持 WordPress 6.9+、PHP 8.3+；目前实际验证环境为 WordPress 7.1.3、PHP 8.5.10、MySQL 8.4.11、Nginx。

主题后台提供个人卡片、横幅、显示与文章、字体、资源、自定义 CSS、升级和备份设置。界面支持简体中文与英文，文章标题与正文保持原语言。访客可切换语言、明暗模式和主题色。

个人卡片支持自定义名称，或跟随指定用户的用户名、昵称。头像可从媒体库选择，也可填写 HTTP(S) URL、站内路径，如 `/wp-content/uploads/avatar.webp`。服务器绝对路径仅接受网站根目录内的文件，并转换为站内 URL；浏览器不能直接读取任意服务器目录。留空使用主题附带头像。社交链接可逐条添加 GitHub、Telegram、X 等平台或多个账号，未填写网址的条目不显示，旧版 `名称|图标|网址` 配置自动兼容。邮箱链接使用 `mailto:`。

默认中文跟随系统字体：苹果优先苹方，Windows 优先微软雅黑，其他系统使用可用的中文无衬线字体，无需下载中文字体。英文使用本地 Roboto，代码使用本地 JetBrains Mono。中文可另选仿宋_GB2312 或上传 WOFF2/WOFF 自定义字体，字体文件存储于媒体库，主题升级后保留。

## 古腾堡编辑器

使用 WordPress 原生区块编辑器。若 Classic Editor 或其他插件强制使用旧编辑器，请停用它或允许切换至区块编辑器。

`theme.json` 提供主题色、字体、字号、间距、边框、渐变和阴影控件。前台和编辑区共用内容样式，覆盖文字、标题、列表、引用、图片、图库、音视频、嵌入、表格、文件、按钮、封面、媒体与文字、分栏、群组、折叠详情、查询、小工具和脚注等区块。原生区块的可用功能仍取决于 WordPress 版本、插件和区块本身的支持项。

主题包含四个区块：

- **Fuwari 代码**：编辑区高亮预览，设置语言、文件名和行号，支持与原生代码区块相互转换；前台提供复制按钮。
- **Fuwari 提示框**：五种类型，可嵌套段落、列表等区块。
- **Fuwari 公式**：LaTeX 即时预览，可选择独立公式或行内公式。
- **Fuwari GitHub 卡片**：输入 `owner/repo`，从服务器获取并缓存公开仓库信息。

插入器搜索 `Fuwari` 可找到主题区块及文章起步、提示与正文、双栏内容、内容卡片四种样板。原生区块另提供导语、突出引用、图片边框、内容卡片、紧凑表格、主题色分隔线六种样式。

文章右侧「Fuwari 文章设置」可设置正文语言、首页卡片摘要及编辑区明暗预览，页面支持正文语言设置。明暗预览只影响当前浏览器的编辑区。横幅和侧栏继续通过主题设置管理；此版本为经典主题的内容区块适配，未迁移为全站区块主题。宽幅、全宽区块限制在文章卡片内，保留 Fuwari 侧栏布局。

旧 Fuwari 代码、提示框区块的保存结构保持兼容。短代码继续可用：

```text
[fuwari_github repo="saicaca/fuwari"]
[fuwari_note type="tip" title="标题"]内容[/fuwari_note]
```

站外嵌入所需资源遵循相应服务的加载行为。主题资源本地化不等于将第三方视频、社交嵌入或用户指定的远程头像镜像到本地。第三方插件区块需要按插件实际样式另行验证。

## 评论

评论区使用主题圆角卡片、主题色和明暗模式，包含评论列表、作者标识、回复层级、分页、审核提示、访客表单及登录状态。文字头像使用称呼的首字，避免为评论头像加载远程 Gravatar。

在主题「显示与文章」开启评论区域，并在文章「讨论」中允许评论。审核、嵌套层数、分页、登录要求和昵称邮箱要求仍由「设置 → 讨论」控制。主题不绕过审核。回复和取消回复使用 WordPress 原生脚本，评论提交采用原生处理流程。

同一设置页提供「评论算术验证码」开关，通用主题默认关闭。开启后，前台访客和已登录用户均需回答 100 以内的加减法，结果为 0–100，减法不会出现负数。题目可刷新，有效期为 15 分钟；服务端校验签名、所属文章、答案、有效期及已提交状态，不在表单中保存答案。后台管理员回复不受影响。页面加载时从本站刷新题目，避免页面缓存留下过期题目；无 JavaScript 时仍可使用服务器输出的题目。启用后，通过 REST 创建评论也需要提供 `fuwari_captcha_token` 和 `fuwari_captcha_answer`。算术题用于基础防刷，不能取代 WordPress 审核或专业反垃圾服务。

页脚位于完整侧栏和文章区域之后，采用普通文档流。短页面保持在页面底部，长页面随内容滚动。

## 本地资源与更新

CSS、JS、字体和 SVG 图标默认本地加载。CDN 目录须镜像完整 `assets` 内容，可分别配置 CSS、JS 和原版字体。自定义字体保持所选媒体 URL。编辑器资源始终从本主题目录加载。GitHub 卡片由服务器访问 GitHub API，访客浏览器不直接请求该 API。

主题设置存储于数据库 `fuwari_settings`，升级时保留。支持 WordPress 原生 ZIP 覆盖升级，默认更新仓库为 `tcpqueue/fuwari-wordpress`，自动更新默认关闭。也可配置 HTTPS 更新清单：

```json
{"version":"1.1.0","package":"https://example.com/fuwari-wp-1.1.0.zip","url":"https://example.com/releases/1.1.0","requires":"6.9","requires_php":"8.3","sha256":"64位SHA256校验值"}
```

升级前自动备份已安装主题至 `wp-content/fuwari-backups/`，可在主题后台回退；Nginx 应禁止外部访问该目录。主题备份不包含数据库和上传目录，需独立保留这些数据。上游 Astro 仓库不能直接作为 WordPress 更新包。

## 开发和验证

Node.js 22+、pnpm 9.14.4。Python 工具使用 uv、CPython 3.12.14 和项目内 `.venv`。开发操作应在 Linux 原生目录进行，`/mnt/...` 仅用于 Windows/WSL 文件传输。

1. 执行 `pnpm install --frozen-lockfile`。
2. 执行 `pnpm build:reference`，准备固定版本的上游源码。
3. 执行 `pnpm build`。已有上游目录可通过 `FUWARI_REFERENCE` 指定。
4. 执行 `uv sync --locked`，将新源码纳入 Git 后，执行 `uv run python scripts/package.py` 生成安装 ZIP、源码 ZIP 和 SHA256 校验文件。

版本号同步维护于主题头、`FUWARI_VERSION`、`package.json` 和 `pyproject.toml`。GitHub Actions 在推送版本标签时构建并发布附件。只运行主题的服务器不需要 Node.js 常驻。

在测试 WordPress 环境执行 `wp eval-file tests/wordpress.php`，检查区块注册、样板、字体、构建入口、头像路径、社交链接、旧短代码、文章/页面 REST 元数据、评论审核及算术验证码的答案、范围、过期、签名和重复提交校验。脚本会创建临时草稿和评论并移至回收站，请先备份测试环境。浏览器验收需额外检查区块插入、设置、保存后重新打开、前台预览、移动端、原生图片和评论交互。

首页、归档和文章布局按固定版 Fuwari 移植。中文字体随访客系统变化；尚未完成所有页面和浏览器的逐像素差分验收，CDN 选项尚未接入实际 CDN。Hexo 历史文章迁移需独立处理正文、链接和附件。

## 许可证

WordPress 主题 PHP 代码使用 GPL-2.0-or-later，移植的 Fuwari 部分保留 MIT 许可证及原作者版权声明。Roboto、JetBrains Mono、KaTeX 及前端库保留各自许可证。通用主题包使用上游附图，不包含本站独立上传的字体及横幅。
