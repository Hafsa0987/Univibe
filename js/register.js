document.getElementById('registerForm').addEventListener('submit',async e=>{
 e.preventDefault();const f=new FormData(e.target);
 try{await api('register',{full_name:f.get('full_name'),university_id:f.get('university_id'),email:f.get('email'),password:f.get('password')});showAlert(document.getElementById('registerAlert'),'Account created! You can now log in.');e.target.reset();setTimeout(()=>location.href='login.html',1200)}
 catch(err){showAlert(document.getElementById('registerAlert'),err.message,'error')}
});
