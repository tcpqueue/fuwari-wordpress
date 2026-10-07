document.addEventListener('click',event=>{
 const add=event.target.closest('.fuwari-social-add');if(add){const editor=add.closest('.fuwari-social-editor'),rows=editor.querySelector('.fuwari-social-rows');const index=String(Date.now())+'_'+String(Math.random()).slice(2,8);const html=editor.querySelector('template').innerHTML.replaceAll('__INDEX__',index);rows.insertAdjacentHTML('beforeend',html);rows.lastElementChild.querySelector('select').focus();return;}
 const remove=event.target.closest('.fuwari-social-remove');if(remove){remove.closest('.fuwari-social-row').remove();return;}
 const button=event.target.closest('.fuwari-media');if(!button)return;
 const frame=wp.media({title:button.textContent,button:{text:button.textContent},multiple:false,library:button.dataset.kind==='media'?{type:'image'}:{}});
 frame.on('select',()=>{const media=frame.state().get('selection').first().toJSON();document.getElementById(button.dataset.target).value=media.url;});frame.open();
});
