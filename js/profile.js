const pf=document.getElementById('profileForm'), pa=document.getElementById('profileAlert');
(async()=>{try{const r=await api('profile');Object.keys(r.user).forEach(k=>{if(pf.elements[k])pf.elements[k].value=r.user[k]??''})}catch(e){if(e.message.toLowerCase().includes('log in'))location.href='login.html';else showAlert(pa,e.message,'error')}})();
pf.onsubmit=async e=>{e.preventDefault();const d=Object.fromEntries(new FormData(pf).entries());try{await api('update_profile',d);showAlert(pa,'Your profile has been updated.')}catch(err){showAlert(pa,err.message,'error')}};
document.getElementById('profileLogout').onclick=async()=>{await api('logout');location.href='index.html'};
