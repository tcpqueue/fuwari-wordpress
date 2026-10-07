document.addEventListener('click',event=>{
 const button=event.target.closest('.fuwari-media');if(!button)return;
 const frame=wp.media({title:button.textContent,button:{text:button.textContent},multiple:false,library:button.dataset.kind==='media'?{type:'image'}:{}});
 frame.on('select',()=>{const media=frame.state().get('selection').first().toJSON();document.getElementById(button.dataset.target).value=media.url;});frame.open();
});
