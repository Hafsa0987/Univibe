const API = 'api.php';
async function api(action, data = {}) {
  const response = await fetch(`${API}?action=${encodeURIComponent(action)}`, {
    method: 'POST', headers: {'Content-Type':'application/json'},
    credentials: 'same-origin', body: JSON.stringify(data)
  });
  const result = await response.json();
  if (!response.ok || result.success === false) throw new Error(result.message || 'Something went wrong.');
  return result;
}
function showAlert(element, message, type='success') {
  if (!element) return;
  element.textContent = message; element.className = `alert show ${type}`;
}
function formatDate(date) {
  return new Date(`${date}T00:00:00`).toLocaleDateString(undefined,{day:'numeric',month:'short',year:'numeric'});
}
function eventCard(event) {
  const img = event.image_url || 'assets/images/event-01.jpg';
  return `<article class="event-card"><img src="${img}" onerror="this.onerror=null;this.src='assets/images/event-01.jpg'" alt="${escapeHtml(event.title)}"><div class="event-body"><span class="tag">${escapeHtml(event.category)}</span><h3>${escapeHtml(event.title)}</h3><div class="meta"><span>◷ ${formatDate(event.event_date)} · ${event.start_time.slice(0,5)}</span><span>⌖ ${escapeHtml(event.venue)}</span></div><div class="event-bottom"><span class="seats">${event.seats_left} seats left</span><a class="btn btn-soft btn-sm" href="event-details.html?id=${event.id}">View details →</a></div></div></article>`;
}
function escapeHtml(value='') {
  return String(value).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

