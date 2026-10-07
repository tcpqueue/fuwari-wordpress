import {renderToString} from 'katex';
import 'katex/dist/katex.min.css';

const wp=window.wp, el=wp.element.createElement;
const {Fragment,useState,useEffect}=wp.element;
const {InspectorControls,useBlockProps,InnerBlocks,PlainText}=wp.blockEditor;
const {PanelBody,TextControl,TextareaControl,ToggleControl,SelectControl}=wp.components;
const settings=window.FuwariEditor||{}, t=(key)=>settings.labels?.[key]||key;
const escapeHTML=value=>String(value).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
let highlighterPromise,shiki;
async function highlight(content,language){
 highlighterPromise ||= import('shiki').then(module=>{shiki=module;return module.createHighlighter({themes:['github-dark'],langs:[]});});
 const highlighter=await highlighterPromise;
 if((shiki.bundledLanguages[language]||shiki.bundledLanguagesAlias?.[language])&&!highlighter.getLoadedLanguages().includes(language))await highlighter.loadLanguage(language);
 return highlighter.codeToHtml(content,{lang:highlighter.getLoadedLanguages().includes(language)?language:'text',theme:'github-dark'});
}
function CodePreview({content,language,filename,lines}){
 const [html,setHTML]=useState('');
 useEffect(()=>{let active=true;const timer=setTimeout(()=>{if(!content){setHTML('');return;}highlight(content,language).then(value=>{if(active)setHTML(value);}).catch(()=>{if(active)setHTML('<pre class="shiki"><code>'+escapeHTML(content)+'</code></pre>');});},150);return()=>{active=false;clearTimeout(timer);};},[content,language]);
 if(!content)return el('div',{className:'fuwari-editor-placeholder'},t('emptyCode'));
 return el('div',{className:'fuwari-editor-code-shell'},el('div',{className:'fuwari-editor-code-header'},el('span',null,filename||language),el('span',null,filename?language:'')),el('div',{className:'fuwari-editor-preview','data-lines':lines?'1':'0',dangerouslySetInnerHTML:{__html:html||'<pre class="shiki"><code>'+escapeHTML(content)+'</code></pre>'}}));
}
const codeAttributes={content:{type:'string',default:''},language:{type:'string',default:'javascript'},filename:{type:'string',default:''},lines:{type:'boolean',default:true}};
function saveCode({attributes:a}){return el('pre',useBlockProps.save({'data-fuwari-language':a.language,'data-fuwari-filename':a.filename,'data-fuwari-lines':a.lines?'1':'0'}),el('code',{className:'language-'+a.language},a.content));}
wp.blocks.registerBlockType('fuwari/code',{
 apiVersion:3,title:t('code'),icon:'editor-code',category:'text',attributes:codeAttributes,
 supports:{html:false,anchor:true,align:['wide','full'],spacing:{margin:true}},
 transforms:{from:[{type:'block',blocks:['core/code'],transform:a=>wp.blocks.createBlock('fuwari/code',{content:wp.htmlEntities.decodeEntities(a.content||''),language:'text'})}],to:[{type:'block',blocks:['core/code'],transform:a=>wp.blocks.createBlock('core/code',{content:escapeHTML(a.content)})}]},
 edit:({attributes:a,setAttributes:set,isSelected})=>{
  const [preview,setPreview]=useState(true);
  return el(Fragment,null,el(InspectorControls,null,el(PanelBody,{title:t('code')},el(TextControl,{label:t('language'),value:a.language,onChange:value=>set({language:value})}),el(TextControl,{label:t('filename'),value:a.filename,onChange:value=>set({filename:value})}),el(ToggleControl,{label:t('lines'),checked:a.lines,onChange:value=>set({lines:value})}),el(ToggleControl,{label:t('preview'),checked:preview,onChange:setPreview}))),el('div',useBlockProps(),preview&&el(CodePreview,a),isSelected&&el('div',{className:'fuwari-editor-source'},el(TextareaControl,{label:t('source'),value:a.content,onChange:value=>set({content:value}),rows:10}))));
 },save:saveCode,
});
const noteAttributes={type:{type:'string',default:'note'},title:{type:'string',default:''}};
wp.blocks.registerBlockType('fuwari/admonition',{
 apiVersion:3,title:t('note'),icon:'info',category:'text',attributes:noteAttributes,
 supports:{html:false,anchor:true,align:['wide','full'],spacing:{margin:true,padding:true}},
 edit:({attributes:a,setAttributes:set,isSelected})=>el(Fragment,null,el(InspectorControls,null,el(PanelBody,{title:t('note')},el(SelectControl,{label:t('type'),value:a.type,options:['note','tip','important','warning','caution'].map(value=>({value,label:t(value+'Type')})),onChange:value=>set({type:value})}),el(TextControl,{label:t('title'),value:a.title,onChange:value=>set({title:value})}))),el('blockquote',useBlockProps({className:'admonition bdm-'+a.type}),el('span',{className:'bdm-title'},isSelected?el(PlainText,{className:'fuwari-editor-plain-title',value:a.title,placeholder:t(a.type+'Type'),'aria-label':t('title'),onChange:value=>set({title:value})}):a.title||a.type.toUpperCase()),el(InnerBlocks,{template:[['core/paragraph',{}]],renderAppender:InnerBlocks.ButtonBlockAppender}))),
 save:({attributes:a})=>el('blockquote',useBlockProps.save({className:'admonition bdm-'+a.type}),el('span',{className:'bdm-title'},a.title||a.type.toUpperCase()),el(InnerBlocks.Content)),
});
wp.blocks.registerBlockType('fuwari/math',{
 apiVersion:3,title:t('math'),icon:'editor-customchar',category:'text',attributes:{content:{type:'string',default:''},display:{type:'boolean',default:true}},
 supports:{html:false,anchor:true,align:['wide','full'],spacing:{margin:true}},
 edit:({attributes:a,setAttributes:set,isSelected})=>{
  const html=renderToString(a.content||'\\LaTeX',{displayMode:a.display,throwOnError:false,trust:false});
  return el(Fragment,null,el(InspectorControls,null,el(PanelBody,{title:t('math')},el(ToggleControl,{label:t('display'),checked:a.display,onChange:value=>set({display:value})}))),el('div',useBlockProps({className:'fuwari-math'}),el('div',{className:'fuwari-editor-math-preview',dangerouslySetInnerHTML:{__html:html}}),isSelected&&el('div',{className:'fuwari-editor-source'},el(TextareaControl,{label:t('latex'),value:a.content,onChange:value=>set({content:value}),rows:4}))));
 },
 save:({attributes:a})=>el('div',useBlockProps.save({className:'fuwari-math'}),(a.display?'\\[':'\\(')+a.content+(a.display?'\\]':'\\)')),
});
const ServerSideRender=wp.serverSideRender.default||wp.serverSideRender;
wp.blocks.registerBlockType('fuwari/github',{
 apiVersion:3,title:t('github'),icon:'admin-links',category:'embed',attributes:{repo:{type:'string',default:''}},
 supports:{html:false,anchor:true,align:['wide','full'],spacing:{margin:true}},
 edit:({attributes:a,setAttributes:set,isSelected})=>{
  const valid=/^[\w.-]+\/[\w.-]+$/.test(a.repo);
  return el(Fragment,null,el(InspectorControls,null,el(PanelBody,{title:t('github')},el(TextControl,{label:t('repo'),help:t('repoHelp'),value:a.repo,onChange:value=>set({repo:value.trim()})}))),el('div',useBlockProps(),(isSelected||!a.repo)&&el(TextControl,{label:t('repo'),help:t('repoHelp'),value:a.repo,onChange:value=>set({repo:value.trim()})}),valid?el(ServerSideRender,{block:'fuwari/github',attributes:a,skipBlockSupportAttributes:true}):el('div',{className:'fuwari-editor-placeholder'},a.repo?t('repoInvalid'):t('repoHelp'))));
 },save:()=>null,
});

function applyCanvasMode(mode){
 const dark=mode==='dark'||(mode==='theme'&&(settings.mode==='dark'||(settings.mode==='system'&&matchMedia('(prefers-color-scheme: dark)').matches)));
 const roots=[...document.querySelectorAll('.editor-styles-wrapper')];
 const frame=document.querySelector('iframe[name="editor-canvas"]');
 if(frame?.contentDocument?.body)roots.push(frame.contentDocument.body);
 roots.forEach(root=>root.classList.toggle('fuwari-editor-dark',dark));
}
function PostSettings(){
 const postType=wp.data.useSelect(select=>select('core/editor').getCurrentPostType(),[]);
 const meta=wp.data.useSelect(select=>select('core/editor').getEditedPostAttribute('meta')||{},[]);
 const {editPost}=wp.data.useDispatch('core/editor');
 const [mode,setMode]=useState(()=>{try{return localStorage.getItem('fuwari-editor-mode')||'theme';}catch{return 'theme';}});
 useEffect(()=>{
  applyCanvasMode(mode);
  const onLoad=()=>applyCanvasMode(mode),frames=new Set();
  const sync=()=>{applyCanvasMode(mode);document.querySelectorAll('iframe[name="editor-canvas"]').forEach(frame=>{if(!frames.has(frame)){frames.add(frame);frame.addEventListener('load',onLoad);}});};
  const observer=new MutationObserver(sync);observer.observe(document.body,{childList:true,subtree:true});sync();
  const media=matchMedia('(prefers-color-scheme: dark)');media.addEventListener('change',onLoad);
  try{localStorage.setItem('fuwari-editor-mode',mode);}catch{}
  return()=>{observer.disconnect();frames.forEach(frame=>frame.removeEventListener('load',onLoad));media.removeEventListener('change',onLoad);};
 },[mode]);
 if(!['post','page'].includes(postType))return null;
 const Panel=wp.editor.PluginDocumentSettingPanel||wp.editPost.PluginDocumentSettingPanel;
 return el(Panel,{name:'fuwari-post-settings',title:t('postSettings'),icon:'edit'},el(SelectControl,{label:t('postLanguage'),value:meta._fuwari_language||'',options:[{label:t('original'),value:''},{label:'简体中文',value:'zh-CN'},{label:'English',value:'en'}],onChange:value=>editPost({meta:{...meta,_fuwari_language:value}})}),postType==='post'&&el(TextareaControl,{label:t('description'),help:t('descriptionHelp'),value:meta._fuwari_description||'',onChange:value=>editPost({meta:{...meta,_fuwari_description:value}})}),el(SelectControl,{label:t('canvasMode'),value:mode,options:[{label:t('theme'),value:'theme'},{label:t('light'),value:'light'},{label:t('dark'),value:'dark'}],onChange:setMode}));
}
wp.plugins.registerPlugin('fuwari-post-settings',{render:PostSettings});
