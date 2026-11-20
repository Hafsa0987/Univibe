/* UniVibe accessible off-canvas navigation */
(() => {
 const sidebar=document.getElementById('dashSidebar'), toggle=document.getElementById('drawerToggle'), overlay=document.getElementById('drawerOverlay'), close=document.getElementById('drawerClose');
 if(!sidebar||!toggle||!overlay)return;
 const open=()=>{sidebar.classList.add('open');overlay.classList.add('visible');document.body.classList.add('drawer-open');sidebar.setAttribute('aria-hidden','false');toggle.setAttribute('aria-expanded','true');close?.focus();};
 const shut=(restore=false)=>{sidebar.classList.remove('open');overlay.classList.remove('visible');document.body.classList.remove('drawer-open');sidebar.setAttribute('aria-hidden','true');toggle.setAttribute('aria-expanded','false');if(restore)toggle.focus();};
 toggle.addEventListener('click',e=>{e.preventDefault();sidebar.classList.contains('open')?shut():open();});
 close?.addEventListener('click',()=>shut(true));overlay.addEventListener('click',()=>shut(true));
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&sidebar.classList.contains('open'))shut(true);});
 sidebar.querySelectorAll('.side-nav a').forEach(a=>a.addEventListener('click',()=>shut()));
 const current=location.pathname.split('/').pop()||'student-dashboard.html';
 sidebar.querySelectorAll('.side-nav a').forEach(a=>{const href=a.getAttribute('href')||'';if(href===current){a.classList.add('active');a.setAttribute('aria-current','page');}else if(!href.startsWith('#')){a.classList.remove('active');a.removeAttribute('aria-current');}});
 shut();
})();
