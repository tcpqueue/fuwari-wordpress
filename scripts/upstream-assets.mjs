import fs from 'node:fs';
import path from 'node:path';
import {execFileSync} from 'node:child_process';
const ref = path.resolve(process.env.FUWARI_REFERENCE || '.cache/fuwari-reference');
const destination = 'theme/assets';
fs.mkdirSync(`${destination}/upstream`, {recursive:true});
fs.mkdirSync(`${destination}/images`, {recursive:true});
const stripScope = text => text.replace(/\[data-astro-cid-[a-z0-9]+\]/g, '').replace(/\.(?:astro|svelte)-[a-zA-Z0-9_-]+/g, '').replace(/:(?:where|is)\(\s*\)/g,'');
let css = '';
for (const file of fs.readdirSync(`${ref}/dist/_astro`)) {
  if (file.endsWith('.css')) {
    css += '\n' + stripScope(fs.readFileSync(`${ref}/dist/_astro/${file}`, 'utf8')).replace(/url\((['"]?)(?:\.\/|\/_astro\/)?([^)'"/]+\.(?:woff2?|ttf|otf))\1\)/g, 'url(upstream/$2)');
  } else if (/\.(woff2?|ttf|otf)$/.test(file)) {
    fs.copyFileSync(`${ref}/dist/_astro/${file}`, `${destination}/upstream/${file}`);
  }
}
const html = fs.readFileSync(`${ref}/dist/posts/expressive-code/index.html`, 'utf8');
const embedded = new Set([...html.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(m => m[1]));
for (const style of embedded) if (style.includes('.expressive-code') || style.includes('.ec-')) css += '\n' + stripScope(style);
const faces=[];
css=css.replace(/@font-face\s*\{[^}]*\}/g, face=>{faces.push(face);return '';});
execFileSync(process.execPath,['node_modules/tailwindcss/lib/cli.js','-c','tailwind.config.cjs','-i','frontend/utilities.css','-o','work/utilities.css','--minify'],{stdio:'inherit'});
css+='\n'+fs.readFileSync('work/utilities.css','utf8');
fs.writeFileSync(`${destination}/upstream.css`, css);
fs.writeFileSync(`${destination}/fonts.css`, [...new Set(faces)].join('\n'));
fs.mkdirSync('theme/licenses',{recursive:true});
for(const pkg of ['@fontsource/roboto','@fontsource-variable/jetbrains-mono','katex','photoswipe','overlayscrollbars','swup','@swup/head-plugin','@swup/preload-plugin','@swup/scroll-plugin','@swup/plugin','shiki','@shikijs/core','@shikijs/engine-oniguruma','@shikijs/vscode-textmate','@shikijs/langs','@shikijs/themes','@shikijs/types','@shikijs/engine-javascript','hast-util-to-html','oniguruma-to-es','regex','regex-recursion']) {
 let folder=`node_modules/${pkg}`;
 if(!fs.existsSync(folder))folder=`${ref}/node_modules/${pkg}`;
 if(!fs.existsSync(folder)){const pnpm='node_modules/.pnpm';const entry=fs.readdirSync(pnpm).find(name=>name.startsWith(pkg.replace('/','+')+'@'));if(entry)folder=`${pnpm}/${entry}/node_modules/${pkg}`;}
 if(!fs.existsSync(folder))continue;
 for(const file of ['LICENSE','LICENSE.md','LICENSE.txt','LICENSE.MIT','OFL.txt'])if(fs.existsSync(`${folder}/${file}`))fs.copyFileSync(`${folder}/${file}`,`theme/licenses/${pkg.replaceAll('/','-').replace('@','')}-${file}`);
}
for (const file of ['demo-avatar.png', 'demo-banner.png']) fs.copyFileSync(`${ref}/src/assets/images/${file}`, `${destination}/images/${file}`);
const icons = {};
for (const set of ['material-symbols', 'fa6-brands', 'fa6-solid', 'fa6-regular']) {
  const data = JSON.parse(fs.readFileSync(`${ref}/node_modules/@iconify-json/${set}/icons.json`, 'utf8'));
  let names = Object.keys(data.icons);
  if (set === 'material-symbols') names = names.filter(n => /^(home-outline-rounded|palette-outline|menu-rounded|light-mode-outline-rounded|dark-mode-outline-rounded|routine-outline|search|calendar-today-outline-rounded|edit-calendar-outline-rounded|update-rounded|book-2-outline-rounded|tag-rounded|chevron-right-rounded|chevron-left-rounded|copyright-outline-rounded|notes-rounded|schedule-outline-rounded|expand-less-rounded|close-rounded|arrow-upward-rounded|language|restart-alt-rounded|light-mode-rounded|dark-mode-rounded|settings-outline-rounded|content-copy-rounded|check-rounded|rss-feed-rounded|mail-outline-rounded)$/.test(n));
  if (set === 'fa6-solid') names = names.filter(n => /^(arrow-up-right-from-square|arrow-rotate-left|chevron-right|chevron-left|hashtag|calendar-days|clock|book|arrow-up|xmark|check|copy|envelope|rss|angle-left|angle-right|circle-half-stroke)$/.test(n));
  if (set === 'fa6-regular') names = names.filter(n => /^(sun|moon|calendar|clock|copyright)$/.test(n));
  for (const name of names) icons[`${set}:${name}`] = {width:data.icons[name].width || data.width || 24, height:data.icons[name].height || data.height || 24, body:data.icons[name].body};
}
fs.writeFileSync(`${destination}/icons.json`, JSON.stringify(icons));
fs.copyFileSync(`${ref}/LICENSE`, 'theme/LICENSE-Fuwari');
console.log(`Upstream CSS ${css.length} bytes; ${Object.keys(icons).length} local SVG icons.`);
