async function loadStudent(){
 try{
  const r=await api('student_dashboard');document.getElementById('studentWelcome').textContent=`Welcome, ${r.user.full_name}. Here's what's happening with your events.`;document.getElementById('sideUserName').textContent=r.user.full_name;
  document.getElementById('studentStats').innerHTML=[['Registered events',r.stats.registered],['Attended',r.stats.attended],['Upcoming',r.stats.upcoming],['Announcements',r.stats.announcements]].map(x=>`<div class="stat-card"><span>${x[0]}</span><strong>${x[1]}</strong></div>`).join('');
  document.getElementById('myRegistrationsList').innerHTML=r.registrations.map(x=>`<div class="registration-row"><div><h3>${escapeHtml(x.title)}</h3><p>${formatDate(x.event_date)} · ${escapeHtml(x.venue)}</p></div><span class="tag">${x.attendance?'Attended':'Registered'}</span></div>`).join('')||'<div class="empty">You have not registered for an event yet. <a href="events.html">Explore events</a>.</div>';
  document.getElementById('studentAnnouncements').innerHTML=r.announcements.map(x=>`<div class="announcement-row"><div><h3>${escapeHtml(x.title)}</h3><p>${escapeHtml(x.message)}</p></div></div>`).join('')||'<p class="muted">No announcements yet.</p>';
 }catch(e){if(e.message.toLowerCase().includes('log in'))location.href='login.html';else showAlert(document.getElementById('studentAlert'),e.message,'error')}
}
document.getElementById('logoutBtn').onclick=async()=>{await api('logout');location.href='index.html'};

document.getElementById('suggestEventForm').onsubmit=async e=>{e.preventDefault();const data=Object.fromEntries(new FormData(e.target).entries());try{const r=await api('suggest_event',data);showAlert(document.getElementById('studentAlert'),r.message);e.target.reset()}catch(err){showAlert(document.getElementById('studentAlert'),err.message,'error')}};
loadStudent();
