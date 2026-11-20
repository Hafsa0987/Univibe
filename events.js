let allEvents=[];
const grid=document.getElementById('eventsGrid');
function renderEvents(){
 const term=document.getElementById('searchEvents').value.toLowerCase();
 const category=document.getElementById('categoryFilter').value;
 let list=allEvents.filter(e=>(!category||e.category===category)&&(`${e.title} ${e.venue} ${e.description}`.toLowerCase().includes(term)));
 if(document.getElementById('sortEvents').value==='seats') list.sort((a,b)=>b.seats_left-a.seats_left);
 else list.sort((a,b)=>a.event_date.localeCompare(b.event_date));
 document.getElementById('eventCount').textContent=`${list.length} event${list.length===1?'':'s'} found`;
 grid.innerHTML=list.map(eventCard).join('')||'<div class="empty">No events match your search. Try another keyword or category.</div>';
}
api('events').then(r=>{allEvents=r.events;renderEvents()}).catch(e=>grid.innerHTML=`<div class="empty">${escapeHtml(e.message)}. Make sure you imported database.sql.</div>`);
['searchEvents','categoryFilter','sortEvents'].forEach(id=>document.getElementById(id).addEventListener(id==='searchEvents'?'input':'change',renderEvents));

// Adapt the explore navigation to the current signed-in session.
(async function updateExploreHeader(){
 const actions=document.getElementById('eventNavActions'); if(!actions)return;
 try { const result=await api('profile'); const user=result.user;
   actions.innerHTML=`<a class="btn btn-outline btn-sm" href="student-dashboard.html">Dashboard</a><a class="btn btn-primary btn-sm" href="profile.html">${escapeHtml(user.full_name.split(' ')[0])} · Profile</a>`; const add=document.querySelector('.banner-add'); if(add){add.href='student-dashboard.html#suggestEvent';add.textContent='＋ Add an event';}
 } catch { /* Guest navigation stays visible */ }
})();
