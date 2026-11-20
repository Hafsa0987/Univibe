(() => {
 const slides=[...document.querySelectorAll('.hero-slide')], dots=[...document.querySelectorAll('.dot')];
 if(slides.length){
  let current=0, timer=null;
  const show=i=>{
   current=(i+slides.length)%slides.length;
   slides.forEach((slide,n)=>{
    slide.classList.toggle('active',n===current);
    slide.setAttribute('aria-hidden',String(n!==current));
   });
   dots.forEach((dot,n)=>{dot.classList.toggle('active',n===current);dot.setAttribute('aria-current',n===current?'true':'false')});
  };
  const restart=()=>{clearInterval(timer);timer=setInterval(()=>show(current+1),4500)};
  document.querySelector('.next')?.addEventListener('click',()=>{show(current+1);restart()});
  document.querySelector('.prev')?.addEventListener('click',()=>{show(current-1);restart()});
  dots.forEach((dot,i)=>dot.addEventListener('click',()=>{show(i);restart()}));
  document.addEventListener('visibilitychange',()=>{if(document.hidden)clearInterval(timer);else restart()});
  show(0);restart();
 }
 const target=document.getElementById('homeEvents');
 if(target) api('events').then(r=>{target.innerHTML=r.events.slice(0,3).map(eventCard).join('')||'<div class="empty">No upcoming events yet. Check back soon.</div>'}).catch(()=>target.innerHTML='<div class="empty">Could not load events. Check that PHP and MySQL are running.</div>');
})();
