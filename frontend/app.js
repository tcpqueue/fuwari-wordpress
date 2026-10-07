import Swup from 'swup';
import HeadPlugin from '@swup/head-plugin';
import PreloadPlugin from '@swup/preload-plugin';
import ScrollPlugin from '@swup/scroll-plugin';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';
import {OverlayScrollbars} from 'overlayscrollbars';
import 'overlayscrollbars/overlayscrollbars.css';
import renderMath from 'katex/contrib/auto-render';
if(!window.FuwariRuntimeLoaded){
window.FuwariRuntimeLoaded=true;
const config=window.FuwariConfig, $=s=>document.querySelector(s), $$=s=>[...document.querySelectorAll(s)];
const translate=key=>config.dictionary[key]?.[config.lang==='en'?1:0]||key;
function applyLanguage(lang){
 config.lang=lang;localStorage.setItem('fuwari-language',lang);document.cookie=`fuwari_lang=${lang}; Path=/; Max-Age=31536000; SameSite=Lax`;
 $$('[data-i18n]').forEach(el=>el.textContent=translate(el.dataset.i18n));$$('[data-i18n-placeholder]').forEach(el=>el.placeholder=translate(el.dataset.i18nPlaceholder));document.documentElement.lang=lang==='en'?'en':'zh-CN';
 const labels={'search-switch':'search','display-settings-switch':'displaySettings','scheme-switch':'colorMode','nav-menu-switch':'menu','hue-reset':'reset','colorSlider':'themeColor','back-to-top':'backToTop','toc-items':'toc'};
 for(const [id,key] of Object.entries(labels))$('#'+id)?.setAttribute('aria-label',translate(key));
 $$('.fuwari-search').forEach(el=>el.setAttribute('aria-label',translate('search')));$$('.fuwari-copy').forEach(el=>el.setAttribute('aria-label',translate('copyCode')));
}
function applyMode(mode){localStorage.setItem('theme',mode);document.documentElement.classList.toggle('dark',mode==='dark'||(mode==='system'&&matchMedia('(prefers-color-scheme: dark)').matches));$$('[data-theme]').forEach(el=>{el.setAttribute('aria-pressed',String(el.dataset.theme===mode));el.classList.toggle('current-theme-btn',el.dataset.theme===mode);});$$('[data-mode-icon]').forEach(el=>el.classList.toggle('hidden',el.dataset.modeIcon!==mode));}
function setHue(value){value=Math.max(0,Math.min(360,Number(value)));document.documentElement.style.setProperty('--hue',value);localStorage.setItem('hue',value);if($('#colorSlider'))$('#colorSlider').value=value;if($('#hue-value'))$('#hue-value').textContent=value;$('#hue-reset')?.classList.toggle('opacity-0',value===config.hue);$('#hue-reset')?.classList.toggle('pointer-events-none',value===config.hue);}
function togglePanel(id){const panel=$('#'+id);if(panel)panel.classList.toggle('float-panel-closed');}
document.addEventListener('click',event=>{
 const theme=event.target.closest('[data-theme]');if(theme){applyMode(theme.dataset.theme);$('#light-dark-panel')?.classList.add('float-panel-closed');}
 const lang=event.target.closest('[data-language]');if(lang)applyLanguage(lang.dataset.language);
 const button=event.target.closest('button');
 if(button?.id==='display-settings-switch')togglePanel('display-setting');
 if(button?.id==='scheme-switch')togglePanel('light-dark-panel');
 if(button?.id==='nav-menu-switch')togglePanel('nav-menu-panel');
 if(button?.id==='search-switch'){togglePanel('search-panel');$('#search-panel input')?.focus();}
 if(button?.id==='hue-reset')setHue(config.hue);
 if(button?.id==='back-to-top')window.scrollTo({top:0,behavior:'smooth'});
 if(!event.target.closest('#navbar'))$$('.float-panel').forEach(p=>p.classList.add('float-panel-closed'));
 if(event.target.closest('#nav-menu-panel a'))$('#nav-menu-panel').classList.add('float-panel-closed');
});
document.addEventListener('keydown',event=>{if(event.key==='Escape')$$('.float-panel').forEach(p=>p.classList.add('float-panel-closed'));});
$('#colorSlider')?.addEventListener('input',event=>setHue(event.target.value));
matchMedia('(prefers-color-scheme: dark)').addEventListener('change',()=>{if((localStorage.getItem('theme')||config.mode)==='system')applyMode('system');});
let searchTimer,searchAbort,searchSerial=0;
function highlighted(text,term){const fragment=document.createDocumentFragment();let start=0,index;while(term&&(index=text.toLocaleLowerCase().indexOf(term.toLocaleLowerCase(),start))!==-1){fragment.append(document.createTextNode(text.slice(start,index)));const mark=document.createElement('mark');mark.textContent=text.slice(index,index+term.length);fragment.append(mark);start=index+term.length;}fragment.append(document.createTextNode(text.slice(start)));return fragment;}
async function search(term){
 const results=$('#search-results'),panel=$('#search-panel');if(!term){searchSerial++;searchAbort?.abort();results.replaceChildren();panel.classList.add('float-panel-closed');return;}
 const serial=++searchSerial;searchAbort?.abort();searchAbort=new AbortController();
 try{const response=await fetch(config.api+'?q='+encodeURIComponent(term),{signal:searchAbort.signal});if(!response.ok)throw new Error('Search failed');const data=await response.json();if(serial!==searchSerial)return;results.replaceChildren();
 for(const item of data){const link=document.createElement('a');link.href=item.url;link.className='transition group block rounded-xl text-lg px-3 py-2 hover:bg-[var(--btn-plain-bg-hover)] active:bg-[var(--btn-plain-bg-active)]';const title=document.createElement('div');title.className='transition text-90 font-bold group-hover:text-[var(--primary)]';title.append(highlighted(item.title,term));const excerpt=document.createElement('div');excerpt.className='transition text-sm text-50';excerpt.append(highlighted(item.excerpt,term));link.append(title,excerpt);link.addEventListener('click',()=>panel.classList.add('float-panel-closed'));results.append(link);}
 if(!data.length){const empty=document.createElement('div');empty.className='p-3 text-50 text-sm';empty.textContent=translate('noResults');results.append(empty);}panel.classList.remove('float-panel-closed');
 }catch(error){if(error.name!=='AbortError'){results.textContent=translate('noResults');panel.classList.remove('float-panel-closed');}}
}
$$('.fuwari-search').forEach(input=>input.addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>search(input.value.trim()),180);}));
let lightbox,tocObserver,highlighterPromise,shikiModule;
async function renderCode(){
 if(!config.code)return;const blocks=$$('.custom-md pre').filter(pre=>!pre.closest('.expressive-code')&&!pre.dataset.enhanced);
 if(!blocks.length)return;
 highlighterPromise ||= import('shiki').then(module=>{shikiModule=module;return module.createHighlighter({themes:['github-dark'],langs:[]});});
 const highlighter=await highlighterPromise;
 for(const pre of blocks){
  pre.dataset.enhanced='1';const code=pre.querySelector('code')||pre;const raw=code.textContent.replace(/\n$/,'');const language=pre.dataset.fuwariLanguage||code.className.match(/language-([\w+-]+)/)?.[1]||'text';
  if((shikiModule.bundledLanguages[language]||shikiModule.bundledLanguagesAlias?.[language])&&!highlighter.getLoadedLanguages().includes(language))await highlighter.loadLanguage(language);
  const known=highlighter.getLoadedLanguages().includes(language)?language:'text';const highlightedCode=highlighter.codeToHtml(raw,{lang:known,theme:'github-dark'});
  const scratch=document.createElement('div');scratch.innerHTML=highlightedCode;const lineElements=[...scratch.querySelectorAll('.line')];
  const wrapper=document.createElement('div');wrapper.className='expressive-code fuwari-code';const figure=document.createElement('figure');figure.className='frame';
  if(pre.dataset.fuwariFilename){figure.classList.add('has-title');const caption=document.createElement('figcaption');caption.className='header';const tab=document.createElement('span');tab.className='title';tab.textContent=pre.dataset.fuwariFilename;caption.append(tab);figure.append(caption);}
  const rendered=document.createElement('pre');rendered.tabIndex=0;const renderedCode=document.createElement('code');
  lineElements.forEach((line,i)=>{const row=document.createElement('div');row.className='ec-line';if(pre.dataset.fuwariLines!=='0'){const number=document.createElement('div');number.className='gutter';number.textContent=i+1;row.append(number);}const text=document.createElement('div');text.className='code';while(line.firstChild)text.append(line.firstChild);row.append(text);renderedCode.append(row);});rendered.append(renderedCode);figure.append(rendered);
  const badge=document.createElement('span');badge.className='fuwari-language-badge';badge.textContent=language;
  const button=document.createElement('button');button.className='copy-btn fuwari-copy';button.setAttribute('aria-label',translate('copyCode'));button.innerHTML='<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V4H4v12h4"/></svg>';button.addEventListener('click',async()=>{try{if(navigator.clipboard&&window.isSecureContext)await navigator.clipboard.writeText(raw);else{const area=document.createElement('textarea');area.value=raw;area.style.position='fixed';area.style.opacity='0';document.body.append(area);area.select();document.execCommand('copy');area.remove();}button.classList.add('success');setTimeout(()=>button.classList.remove('success'),1000);}catch{button.classList.add('error');}});
  figure.append(badge,button);wrapper.append(figure);pre.replaceWith(wrapper);
 }
}
function buildTOC(){
 tocObserver?.disconnect();const toc=$('#toc-items');if(!toc)return;toc.replaceChildren();if($('#toc').dataset.enabled!=='1'||!$('#post-container'))return;
 const headings=$$('.markdown-content h1,.markdown-content h2,.markdown-content h3,.markdown-content h4,.markdown-content h5,.markdown-content h6');const min=Math.min(...headings.map(h=>Number(h.tagName[1])));let count=0;
 const entries=[];for(const [index,h] of headings.entries()){const depth=Number(h.tagName[1])-min;if(depth>=config.tocDepth)continue;h.id ||= 'heading-'+index;const a=document.createElement('a');a.href='#'+h.id;a.className='px-2 flex gap-2 relative transition w-full min-h-9 rounded-xl hover:bg-[var(--toc-btn-hover)] active:bg-[var(--toc-btn-active)] py-2';const badge=document.createElement('div');badge.className='transition w-5 h-5 shrink-0 rounded-lg text-xs flex items-center justify-center font-bold '+(depth===0?'bg-[var(--toc-badge-bg)] text-[var(--btn-content)]':depth===1?'ml-4':'ml-8');if(!depth)badge.textContent=++count;else badge.innerHTML=depth===1?'<div class="transition w-2 h-2 rounded-[0.1875rem] bg-[var(--toc-badge-bg)]"></div>':'<div class="transition w-1.5 h-1.5 rounded-sm bg-black/5 dark:bg-white/10"></div>';const text=document.createElement('div');text.className='transition text-sm text-50';text.textContent=h.textContent.replace(/\s*#$/,'');a.append(badge,text);toc.append(a);entries.push([h,a]);}
 tocObserver=new IntersectionObserver(observed=>{for(const entry of observed){const a=entries.find(([h])=>h===entry.target)?.[1];a?.classList.toggle('fuwari-toc-active',entry.isIntersecting);}},{rootMargin:'-64px 0px -60% 0px'});entries.forEach(([h])=>tocObserver.observe(h));
}
function initLightbox(){
 lightbox?.destroy();if(!config.lightbox)return;
 $$('.custom-md img').forEach(image=>{let link=image.closest('a');if(link&&!/\.(avif|gif|jpe?g|png|webp)(?:[?#]|$)/i.test(link.href))return;if(!link){link=document.createElement('a');link.href=image.src;image.replaceWith(link);link.append(image);}link.classList.add('fuwari-gallery-item');link.dataset.noSwup='1';const update=()=>{link.dataset.pswpWidth=image.naturalWidth;link.dataset.pswpHeight=image.naturalHeight;};if(image.complete)update();else image.addEventListener('load',update,{once:true});});
 lightbox=new PhotoSwipeLightbox({gallery:'.custom-md',children:'.fuwari-gallery-item',pswpModule:()=>import('photoswipe'),bgOpacity:.8,showHideAnimationType:'zoom',padding:{top:20,bottom:20,left:20,right:20},wheelToZoom:true,arrowPrev:false,arrowNext:false,imageClickAction:'close',tapAction:'close',doubleTapAction:'zoom'});lightbox.init();
}
function initPage(){
 applyLanguage(localStorage.getItem('fuwari-language')||config.lang);applyMode(localStorage.getItem('theme')||config.mode);setHue(localStorage.getItem('hue')??config.hue);$('#banner')?.classList.remove('opacity-0','scale-105');
 buildTOC();initLightbox();renderCode().catch(error=>console.warn('Code rendering:',error));
 if(config.math)$$('.custom-md').forEach(element=>renderMath(element,{delimiters:[{left:'$$',right:'$$',display:true},{left:'\\[',right:'\\]',display:true},{left:'\\(',right:'\\)',display:false},{left:'$',right:'$',display:false}],throwOnError:false}));
}
function onScroll(){const y=window.scrollY,threshold=innerHeight*config.innerHeight/100;$('#back-to-top')?.classList.toggle('opacity-0',y<threshold);$('#back-to-top')?.classList.toggle('pointer-events-none',y<threshold);$('#toc-wrapper')?.classList.toggle('toc-hide',config.banner&&y<threshold);if(config.banner){const height=document.body.classList.contains('is-home')&&innerWidth>=1024?config.homeHeight:config.innerHeight;$('#navbar-wrapper')?.classList.toggle('navbar-hidden',y>=innerHeight*height/100-72-56-16);}}
window.addEventListener('scroll',onScroll,{passive:true});
window.addEventListener('resize',()=>{const e=Math.floor(innerHeight*(config.homeHeight-config.innerHeight)/100);document.documentElement.style.setProperty('--banner-height-extend',(e-e%4)+'px');});
if(config.transitions){
 const swup=new Swup({containers:['#swup-container','#toc'],linkSelector:'a[href]:not([data-no-swup]):not([href*="/wp-admin"]):not([href*="/wp-login"]):not([download])',plugins:[new HeadPlugin({persistAssets:true}),new PreloadPlugin(),new ScrollPlugin({animateScroll:true})]});
 swup.hooks.on('visit:start',visit=>{const isHome=new URL(visit.to.url,location.href).pathname===new URL(config.base).pathname;document.body.classList.toggle('is-home',isHome);document.body.classList.toggle('lg:is-home',isHome);});
 swup.hooks.on('page:view',()=>{initPage();onScroll();});window.fuwariSwup=swup;
}
OverlayScrollbars(document.body,{scrollbars:{autoHide:'scroll',autoHideDelay:500}});
initPage();onScroll();
}
