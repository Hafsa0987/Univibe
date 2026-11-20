document.getElementById('loginForm').addEventListener('submit',async e=>{
 e.preventDefault();const f=new FormData(e.target);
 try{const r=await api('login',{email:f.get('email'),password:f.get('password')});showAlert(document.getElementById('loginAlert'),'Login successful. Redirecting…');location.href=r.user.role==='admin'?'admin-dashboard.html':'student-dashboard.html'}
 catch(err){showAlert(document.getElementById('loginAlert'),err.message,'error')}
});
