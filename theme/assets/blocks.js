(function(wp){
 const el=wp.element.createElement, t=window.FuwariEditor||{}, register=wp.blocks.registerBlockType, useProps=wp.blockEditor.useBlockProps;
 register('fuwari/code',{apiVersion:3,title:t.code||'Fuwari Code',icon:'editor-code',category:'text',attributes:{content:{type:'string',default:''},language:{type:'string',default:'javascript'},filename:{type:'string',default:''},lines:{type:'boolean',default:true}},
  edit:({attributes:a,setAttributes:set})=>el('div',useProps(),el(wp.components.TextControl,{label:t.language||'Language',value:a.language,onChange:v=>set({language:v})}),el(wp.components.TextControl,{label:t.filename||'Filename',value:a.filename,onChange:v=>set({filename:v})}),el(wp.components.ToggleControl,{label:t.lines||'Line numbers',checked:a.lines,onChange:v=>set({lines:v})}),el(wp.components.TextareaControl,{label:t.code||'Code',value:a.content,onChange:v=>set({content:v}),rows:10})),
  save:({attributes:a})=>el('pre',useProps.save({'data-fuwari-language':a.language,'data-fuwari-filename':a.filename,'data-fuwari-lines':a.lines?'1':'0'}),el('code',{className:'language-'+a.language},a.content))
 });
 register('fuwari/admonition',{apiVersion:3,title:t.note||'Fuwari Note',icon:'info',category:'text',attributes:{type:{type:'string',default:'note'},title:{type:'string',default:''}},
  edit:({attributes:a,setAttributes:set})=>el('div',useProps(),el(wp.components.SelectControl,{label:t.type||'Type',value:a.type,options:['note','tip','important','warning','caution'].map(v=>({label:v,value:v})),onChange:v=>set({type:v})}),el(wp.components.TextControl,{label:t.title||'Title',value:a.title,onChange:v=>set({title:v})}),el(wp.blockEditor.InnerBlocks)),
  save:({attributes:a})=>el('blockquote',useProps.save({className:'admonition bdm-'+a.type}),el('span',{className:'bdm-title'},a.title||a.type.toUpperCase()),el(wp.blockEditor.InnerBlocks.Content))
 });
})(window.wp);
