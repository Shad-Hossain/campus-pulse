/* ============================================================
   Campus Pulse — frontend logic
   Talks to the PHP API in /api/*.php (JSON over fetch, session-
   cookie auth). Renders the same markup/CSS as the prototype.
   ============================================================ */

/* ---------------- API HELPER ---------------- */
async function api(path, { method = 'GET', json = null, form = null } = {}) {
  const opts = { method, credentials: 'same-origin' };
  if (json) { opts.headers = { 'Content-Type': 'application/json' }; opts.body = JSON.stringify(json); }
  if (form) { opts.body = form; } // browser sets multipart boundary itself
  const res = await fetch(path, opts);
  let data;
  try { data = await res.json(); } catch { data = {}; }
  if (!res.ok) throw new Error(data.error || 'Request failed');
  return data;
}

/* ---------------- ICONS ---------------- */
const ICON = {
  home:'<path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9h12v-9"/>',
  calendar:'<rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M3.5 10h17"/><path d="M8 3v4"/><path d="M16 3v4"/>',
  award:'<circle cx="12" cy="8" r="5"/><path d="M8.2 12.5 6.8 21 12 18l5.2 3-1.4-8.5"/>',
  search:'<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
  grid:'<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
  user:'<circle cx="12" cy="8" r="4"/><path d="M4 20.5c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/>',
  alert:'<path d="M12 3.5 21 19.5H3L12 3.5Z"/><path d="M12 10v4"/><path d="M12 16.5h.01"/>',
  users:'<circle cx="8.5" cy="8" r="3"/><path d="M2.5 20c0-3.3 3-5.5 6-5.5s6 2.2 6 5.5"/><circle cx="17" cy="8.5" r="2.3"/><path d="M14.7 20c.2-2.4 1.7-4 3.7-4.3"/>',
  book:'<path d="M4 5.2C4 4.4 5.6 4 8 4c2.2 0 3.8.8 4 1.4V19c-.2-.6-1.8-1.4-4-1.4-2.4 0-4 .4-4 1.2Z"/><path d="M20 5.2C20 4.4 18.4 4 16 4c-2.2 0-3.8.8-4 1.4V19c.2-.6 1.8-1.4 4-1.4 2.4 0 4 .4 4 1.2Z"/>',
  download:'<path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M4 19h16"/>'
};
function iconSVG(name){return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">${ICON[name]}</svg>`;}

/* ---------------- NAV CONFIG PER ROLE ---------------- */
const NAV = {
  student:[['home','Home feed'],['events','Events'],['resources','Resources'],['achievements','Achievements'],['search','Search & filter'],['dashboard','Dashboard'],['profile','Profile']],
  faculty:[['home','Home feed'],['events','Events'],['resources','Resources'],['grants','Research & grants'],['achievements','Achievements'],['search','Search & filter'],['dashboard','Dashboard'],['profile','Profile']],
  admin:[['home','Home feed'],['events','Events'],['resources','Resources'],['alerts','Manage alerts'],['grants-admin','Manage grants'],['directory','User directory'],['achievements','Achievements'],['search','Search & filter'],['dashboard','Dashboard'],['profile','Profile']]
};
const NAV_ICON = {home:'home',events:'calendar',resources:'book',grants:'award','grants-admin':'award',achievements:'award',alerts:'alert',directory:'users',search:'search',dashboard:'grid',profile:'user'};
const VIEW_TITLE = {home:'Home feed',events:'Events',resources:'Resources',grants:'Research & grants','grants-admin':'Manage grants',achievements:'Achievements',alerts:'Manage alerts',directory:'User directory',search:'Search & filter',dashboard:'Dashboard',profile:'Profile'};

/* ---------------- STATE (populated from the server) ---------------- */
let CURRENT_USER = null;
let NEWS = [], ACHIEVE = [];
let EVENTS = [], PENDING_EVENTS = [];
let RESOURCES = [], PENDING_RESOURCES = [];
let GRANTS = [], MY_GRANTS = [], PENDING_GRANTS = [];
let ACTIVE_ALERTS = [], CAMPUS_STATUS = 'normal';
let DIRECTORY = [];
let ALL_ITEMS = [];

/* ---------------- RENDER HELPERS ---------------- */
function tagClass(cat){ if(cat==='Academic') return 'tag-academic'; if(cat==='Admin') return 'tag-admin'; if(cat==='Competition') return 'tag-competition'; return 'tag-club'; }
function cardHTML(item, actions){
  return `<div class="card">
    <span class="tag-pill ${tagClass(item.category||item.cat)}">${item.category||item.cat}</span>
    <h3>${item.title}</h3><p>${item.description||item.desc||''}</p>
    <div class="meta"><span>${item.meta||''}</span></div>
    ${actions||''}
  </div>`;
}
function render(id, items, actionsFn){
  const el = document.getElementById(id);
  el.innerHTML = items.length ? items.map(i=>cardHTML(i, actionsFn?actionsFn(i):'')).join('') : '<div class="empty-state">No results match this filter.</div>';
}

/* ---------------- HOME / ACHIEVEMENTS ---------------- */
function renderHome(){ render('home-news-grid', NEWS); }
function renderAchievements(){ render('achieve-grid', ACHIEVE); render('grant-grid', GRANTS); }

/* ---------------- EVENTS ---------------- */
function eventCardHTML(item){
  const count = item.interest_count||0; const pct = Math.min(100, count);
  const pinged = item._pinged ? 'pinged' : '';
  return `<div class="card">
    <span class="tag-pill ${tagClass(item.category)}">${item.category}</span>
    <h3>${item.title}</h3><p>${item.description||''}</p>
    <div class="meta"><span>${item.meta||''}</span></div>
    <div class="interest-row">
      <div class="interest-bar"><div class="interest-bar-fill" style="width:${pct}%"></div></div>
      <button class="interest-btn ${pinged}" onclick="pingInterest(${item.id})">🔥 ${count} interested</button>
    </div>
  </div>`;
}
function renderEvents(cat){
  const list = (cat==='all'||!cat) ? EVENTS : EVENTS.filter(i=>i.category===cat);
  document.getElementById('events-grid').innerHTML = list.length ? list.map(eventCardHTML).join('') : '<div class="empty-state">No results match this filter.</div>';
}
window.pingInterest = async function(id){
  try{
    const res = await api('api/events.php', {method:'POST', json:{action:'interest', id}});
    const ev = EVENTS.find(e=>e.id===id);
    if(ev){ ev.interest_count = res.interest_count; ev._pinged = res.pinged; }
    renderEvents(currentEventFilter());
  }catch(e){ alert(e.message); }
};
function currentEventFilter(){
  const active = document.querySelector('#events-filter .chip.active');
  return active ? active.dataset.cat : 'all';
}
function renderPendingEvents(){
  render('events-pending-grid', PENDING_EVENTS, (i)=>{
    const isAdmin = CURRENT_USER.role==='admin';
    return isAdmin ? `<div class="card-actions">
      <button class="mini-btn approve" onclick="approveEvent(${i.id})">Approve</button>
      <button class="mini-btn reject" onclick="rejectEvent(${i.id})">Reject</button></div>`
      : `<div class="meta"><span class="status-pending">Awaiting admin approval</span></div>`;
  });
  document.getElementById('events-pending-block').style.display = PENDING_EVENTS.length ? 'block':'none';
}
window.approveEvent = async function(id){ await api('api/events.php',{method:'POST',json:{action:'approve',id}}); await loadEvents(); renderEvents('all'); renderPendingEvents(); };
window.rejectEvent = async function(id){ await api('api/events.php',{method:'POST',json:{action:'reject',id}}); await loadEvents(); renderPendingEvents(); };
async function loadEvents(){
  const data = await api('api/events.php');
  EVENTS = data.approved; PENDING_EVENTS = data.pending;
}

/* ---------------- RESOURCES HUB ---------------- */
const QUICK_LINKS = [
  {abbr:'UC', name:'UCAM', desc:'Grades, registration'},
  {abbr:'EL', name:'ELMS', desc:'Online class materials'},
  {abbr:'LB', name:'Library', desc:'Catalog & e-resources'},
  {abbr:'RT', name:'Class routine', desc:'Section schedule'},
  {abbr:'CB', name:'Career cell', desc:'Counseling & jobs'},
  {abbr:'CL', name:'Clubs', desc:'Student organizations'},
];
function renderQuickLinks(){
  document.getElementById('qlink-grid').innerHTML = QUICK_LINKS.map(l=>
    `<a class="qlink-tile" href="#" onclick="return false;"><div class="qlink-badge">${l.abbr}</div><b>${l.name}</b><span>${l.desc}</span></a>`).join('');
}
let activeResourceKind = 'notes';
function resourceCardHTML(r){
  return `<div class="card">
    <span class="tag-pill ${r.kind==='notes'?'tag-academic':'tag-competition'}">${r.course_code}</span>
    <h3>${r.title}</h3>
    <div class="meta"><span>Uploaded by ${r.uploader_name||'Unknown'}</span></div>
    <div class="res-download-row">
      <span class="dl-count">${r.downloads} downloads</span>
      <button class="download-btn" onclick="downloadResource(${r.id})">${iconSVG('download')} Download</button>
    </div>
  </div>`;
}
function renderResources(){
  const list = RESOURCES.filter(r=>r.kind===activeResourceKind);
  document.getElementById('resources-grid').innerHTML = list.length ? list.map(resourceCardHTML).join('') : '<div class="empty-state">No uploads in this category yet.</div>';
}
window.downloadResource = async function(id){
  const res = await api('api/resources.php', {method:'POST', json:{action:'download', id}});
  const r = RESOURCES.find(x=>x.id===id);
  if(r){ r.downloads = res.downloads; }
  renderResources();
  if(res.file_path) window.open(res.file_path, '_blank');
};
function renderPendingResources(){
  document.getElementById('resources-pending-grid').innerHTML = PENDING_RESOURCES.map(r=>{
    const isAdmin = CURRENT_USER.role==='admin';
    const actions = isAdmin ? `<div class="card-actions">
      <button class="mini-btn approve" onclick="approveResource(${r.id})">Approve</button>
      <button class="mini-btn reject" onclick="rejectResource(${r.id})">Reject</button></div>`
      : `<div class="meta"><span class="status-pending">Awaiting admin approval</span></div>`;
    return `<div class="card">
      <span class="tag-pill ${r.kind==='notes'?'tag-academic':'tag-competition'}">${r.course_code}</span>
      <h3>${r.title}</h3><div class="meta"><span>Submitted by ${r.uploader_name||'You'}</span></div>
      ${actions}</div>`;
  }).join('');
  document.getElementById('resources-pending-block').style.display = PENDING_RESOURCES.length ? 'block' : 'none';
}
window.approveResource = async function(id){ await api('api/resources.php',{method:'POST',json:{action:'approve',id}}); await loadResources(); renderResources(); renderPendingResources(); };
window.rejectResource = async function(id){ await api('api/resources.php',{method:'POST',json:{action:'reject',id}}); await loadResources(); renderPendingResources(); };
async function loadResources(){
  const data = await api('api/resources.php');
  RESOURCES = data.approved; PENDING_RESOURCES = data.pending;
}

/* ---------------- RESEARCH & GRANTS ---------------- */
function renderMyGrants(){
  render('my-grants-grid', MY_GRANTS.map(g=>({category:'Admin', title:g.title, description:g.description, meta:''})), ()=>'');
  document.querySelectorAll('#my-grants-grid .card').forEach((card,i)=>{
    const st = MY_GRANTS[i].status;
    const cls = st==='approved'?'badge-approved':(st==='rejected'?'badge-rejected':'badge-pending');
    const label = st==='approved'?'Approved':(st==='rejected'?'Not approved':'Pending review');
    const b = document.createElement('span'); b.className = 'badge-status '+cls; b.textContent = label;
    card.querySelector('.meta').appendChild(b);
  });
}
function renderPendingGrants(){
  render('grants-pending-grid', PENDING_GRANTS, (g)=>`<div class="card-actions">
    <button class="mini-btn approve" onclick="approveGrant(${g.id})">Approve</button>
    <button class="mini-btn reject" onclick="rejectGrant(${g.id})">Reject</button></div>`);
  render('grant-grid-admin', GRANTS);
}
window.approveGrant = async function(id){ await api('api/grants.php',{method:'POST',json:{action:'approve',id}}); await loadGrants(); renderPendingGrants(); renderMyGrants(); renderAchievements(); };
window.rejectGrant = async function(id){ await api('api/grants.php',{method:'POST',json:{action:'reject',id}}); await loadGrants(); renderPendingGrants(); renderMyGrants(); };
async function loadGrants(){
  const data = await api('api/grants.php');
  GRANTS = data.approved; MY_GRANTS = data.mine; PENDING_GRANTS = data.pending;
}

/* ---------------- ALERTS (admin) ---------------- */
function renderActiveAlerts(){
  const el = document.getElementById('active-alerts-list');
  el.innerHTML = ACTIVE_ALERTS.map(a=>`<div class="preview-strip"><div class="l">[${a.type}] ${a.message}</div><button class="link-btn" onclick="removeAlert(${a.id})">Remove</button></div>`).join('');
}
window.removeAlert = async function(id){ await api('api/alerts.php',{method:'POST',json:{action:'remove',id}}); await loadAlerts(); renderActiveAlerts(); rebuildTicker(); };
function rebuildTicker(){ document.getElementById('ticker-text').textContent = ACTIVE_ALERTS.map(a=>a.message).join(' · '); }
async function loadAlerts(){
  const data = await api('api/alerts.php');
  ACTIVE_ALERTS = data.alerts; CAMPUS_STATUS = data.status;
}
const STATUS_TEXT = {normal:'Campus status: Normal', alert:'Campus status: Alert — minor disruption', critical:'Campus status: Critical — check alerts'};
function applyStatusPill(){
  const pill = document.getElementById('status-pill');
  pill.className = 'status-pill '+CAMPUS_STATUS;
  document.getElementById('status-pill-text').textContent = STATUS_TEXT[CAMPUS_STATUS];
  document.getElementById('admin-status-select').value = CAMPUS_STATUS;
}
document.getElementById('admin-status-btn').addEventListener('click', async ()=>{
  const val = document.getElementById('admin-status-select').value;
  await api('api/alerts.php', {method:'POST', json:{action:'status', status:val}});
  CAMPUS_STATUS = val; applyStatusPill();
});
document.getElementById('admin-post-btn').addEventListener('click', async ()=>{
  const title = document.getElementById('admin-alert-title').value.trim(); if(!title) return;
  const type = document.getElementById('admin-alert-type').value;
  await api('api/alerts.php', {method:'POST', json:{action:'post', title, type}});
  document.getElementById('admin-alert-title').value='';
  await loadAlerts(); renderActiveAlerts(); rebuildTicker();
});

/* ---------------- DIRECTORY (admin) ---------------- */
async function renderDirectory(q){
  const data = await api('api/directory.php?q='+encodeURIComponent(q||''));
  DIRECTORY = data.users;
  document.getElementById('directory-body').innerHTML = DIRECTORY.map(d=>`<tr><td>${d.name}</td><td>${d.department}</td><td><span class="role-chip ${d.role}">${d.role}</span></td></tr>`).join('');
}
document.getElementById('directory-search').addEventListener('input', (e)=> renderDirectory(e.target.value));

/* ---------------- LOGIN FLOW ---------------- */
let selectedRole = 'student';
document.querySelectorAll('#role-pick button').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    document.querySelectorAll('#role-pick button').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); selectedRole = btn.dataset.role;
  });
});

document.getElementById('login-btn').addEventListener('click', async ()=>{
  const email = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-pass').value;
  const errEl = document.getElementById('login-error');
  errEl.style.display = 'none';
  try{
    const data = await api('api/login.php', {method:'POST', json:{email, password, role:selectedRole}});
    await enterApp(data.user);
  }catch(e){
    errEl.textContent = e.message; errEl.style.display = 'block';
  }
});
document.getElementById('logout-btn').addEventListener('click', async ()=>{
  await api('api/logout.php', {method:'POST'});
  document.getElementById('app-shell').style.display = 'none';
  document.getElementById('login-screen').style.display = 'flex';
  CURRENT_USER = null;
});

function buildSidebar(role){
  const nav = document.getElementById('side-nav');
  nav.innerHTML = NAV[role].map(([v,l],i)=>
    `<button class="nav-btn ${i===0?'active':''}" data-view="${v}">${iconSVG(NAV_ICON[v])}${l}</button>`).join('');
  nav.querySelectorAll('.nav-btn').forEach(btn=> btn.addEventListener('click', ()=>goToView(btn.dataset.view)));
}

async function enterApp(user){
  CURRENT_USER = user;
  document.getElementById('login-screen').style.display = 'none';
  document.getElementById('app-shell').style.display = 'block';
  document.body.className = 'role-'+user.role;
  const label = user.role.charAt(0).toUpperCase()+user.role.slice(1);
  document.getElementById('role-tag').textContent = label;
  document.getElementById('role-tag').className = 'role-tag '+user.role;
  document.getElementById('side-role-label').textContent = user.name;
  document.getElementById('dash-role-label').textContent = label;
  document.getElementById('dash-name').textContent = user.name;
  const initial = user.name.charAt(0).toUpperCase();
  document.getElementById('topbar-avatar').textContent = initial;
  document.getElementById('profile-avatar-fallback').textContent = initial;
  document.getElementById('profile-head-name').textContent = user.name;
  document.getElementById('profile-head-sub').textContent = `${user.department} · ${label}`;
  document.getElementById('profile-name').value = user.name;
  document.getElementById('profile-dept').value = user.department;
  document.getElementById('profile-email').value = user.email;
  document.getElementById('profile-bio').value = user.bio || '';
  if(user.avatar_path){
    const av = document.getElementById('profile-avatar-preview');
    av.src = user.avatar_path; av.style.display='flex'; document.getElementById('profile-avatar-fallback').style.display='none';
    const tb = document.getElementById('topbar-avatar'); tb.style.backgroundImage = `url(${user.avatar_path})`; tb.style.backgroundSize='cover'; tb.textContent='';
  }

  buildSidebar(user.role);
  document.getElementById('event-create-toggle').style.display = (user.role==='faculty'||user.role==='admin') ? 'inline-block':'none';

  // pull everything from the server
  const [feed] = await Promise.all([api('api/feed.php'), loadEvents(), loadResources(), loadGrants(), loadAlerts()]);
  NEWS = feed.news; ACHIEVE = feed.achievements;

  renderHome(); renderAchievements();
  renderEvents('all'); renderPendingEvents();
  renderQuickLinks(); renderResources(); renderPendingResources();
  renderMyGrants(); renderPendingGrants();
  applyStatusPill(); rebuildTicker();
  buildDashboard(user.role);
  if(user.role==='admin'){ renderActiveAlerts(); renderDirectory(''); }

  goToView('home');
}

/* ---------------- DASHBOARD BUILD (role-specific) ---------------- */
function buildDashboard(role){
  const el = document.getElementById('dashboard-left');
  if(role==='student'){
    el.innerHTML = `<div class="stat-row">
        <div class="stat-box"><div class="num">${ACTIVE_ALERTS.length}</div><div class="lbl">Unread alerts</div></div>
        <div class="stat-box"><div class="num">${EVENTS.length}</div><div class="lbl">Events live</div></div>
      </div>
      <div class="section-title">Upcoming events</div>
      <div class="stat-box">
        ${EVENTS.slice(0,4).map(e=>`<div class="saved-item"><span>${e.title}</span><span>${(e.meta||'').split('·')[0]}</span></div>`).join('') || '<div class="saved-item"><span>No events yet</span></div>'}
      </div>`;
  } else if(role==='faculty'){
    el.innerHTML = `<div class="stat-row">
        <div class="stat-box"><div class="num">${EVENTS.length}</div><div class="lbl">Events posted</div></div>
        <div class="stat-box"><div class="num">${MY_GRANTS.length}</div><div class="lbl">Grant submissions</div></div>
      </div>
      <div class="section-title">Recent submissions</div>
      <div class="stat-box">
        ${MY_GRANTS.map(g=>`<div class="saved-item"><span>${g.title}</span><span class="badge-status ${g.status==='approved'?'badge-approved':'badge-pending'}">${g.status==='approved'?'Approved':'Pending'}</span></div>`).join('') || '<div class="saved-item"><span>No submissions yet</span></div>'}
      </div>`;
  } else {
    el.innerHTML = `<div class="stat-row">
        <div class="stat-box"><div class="num">${DIRECTORY.length}</div><div class="lbl">Registered users</div></div>
        <div class="stat-box"><div class="num">${PENDING_EVENTS.length}</div><div class="lbl">Pending approvals</div></div>
      </div>
      <div class="section-title">Platform snapshot</div>
      <div class="stat-box">
        <div class="saved-item"><span>Active alerts on ticker</span><span>${ACTIVE_ALERTS.length}</span></div>
        <div class="saved-item"><span>Events live this week</span><span>${EVENTS.length}</span></div>
      </div>`;
  }
}

/* ---------------- NAV SWITCHING ---------------- */
function goToView(name){
  document.querySelectorAll('.nav-btn').forEach(b=>b.classList.toggle('active', b.dataset.view===name));
  document.querySelectorAll('.view').forEach(v=>v.classList.remove('active'));
  document.getElementById('view-'+name).classList.add('active');
  document.getElementById('view-title').textContent = VIEW_TITLE[name];
  document.getElementById('sidebar-el').classList.remove('open');
}
document.querySelectorAll('[data-goto]').forEach(btn=> btn.addEventListener('click', ()=>goToView(btn.dataset.goto)));
document.getElementById('mobile-menu-btn')?.addEventListener('click', ()=> document.getElementById('sidebar-el').classList.toggle('open'));

/* ---------------- BELL ---------------- */
const bellMenu = document.getElementById('bell-menu');
document.getElementById('bell-btn').addEventListener('click', (e)=>{ e.stopPropagation(); bellMenu.classList.toggle('open'); });
document.addEventListener('click', ()=> bellMenu.classList.remove('open'));

/* ---------------- EVENTS: filter + create + pending ---------------- */
document.getElementById('events-filter').addEventListener('click', (e)=>{
  if(!e.target.classList.contains('chip')) return;
  document.querySelectorAll('#events-filter .chip').forEach(c=>c.classList.remove('active'));
  e.target.classList.add('active'); renderEvents(e.target.dataset.cat);
});
document.getElementById('event-create-toggle').addEventListener('click', ()=> document.getElementById('event-form').classList.toggle('open'));
document.getElementById('ev-submit').addEventListener('click', async ()=>{
  const title = document.getElementById('ev-title').value.trim(); if(!title) return;
  const category = document.getElementById('ev-cat').value; const meta = document.getElementById('ev-meta').value.trim();
  try{
    await api('api/events.php', {method:'POST', json:{action:'create', title, category, meta}});
    document.getElementById('ev-title').value=''; document.getElementById('ev-meta').value='';
    document.getElementById('event-form').classList.remove('open');
    await loadEvents(); renderEvents('all'); renderPendingEvents(); buildDashboard(CURRENT_USER.role);
  }catch(e){ alert(e.message); }
});

/* ---------------- RESOURCES HUB: tabs + upload ---------------- */
document.getElementById('resource-tabs').addEventListener('click', (e)=>{
  if(!e.target.classList.contains('chip')) return;
  document.querySelectorAll('#resource-tabs .chip').forEach(c=>c.classList.remove('active'));
  e.target.classList.add('active'); activeResourceKind = e.target.dataset.kind; renderResources();
});
document.getElementById('resource-upload-toggle').addEventListener('click', ()=> document.getElementById('resource-form').classList.toggle('open'));
document.getElementById('res-submit').addEventListener('click', async ()=>{
  const course = document.getElementById('res-course').value.trim(); const title = document.getElementById('res-title').value.trim();
  if(!course || !title) return;
  const kind = document.getElementById('res-kind').value;
  const fileInput = document.getElementById('res-file');
  const fd = new FormData();
  fd.append('action','create'); fd.append('course_code', course); fd.append('title', title); fd.append('kind', kind);
  if(fileInput.files[0]) fd.append('file', fileInput.files[0]);
  try{
    await api('api/resources.php', {method:'POST', form:fd});
    document.getElementById('res-course').value=''; document.getElementById('res-title').value=''; fileInput.value='';
    document.getElementById('resource-form').classList.remove('open');
    await loadResources(); renderResources(); renderPendingResources();
  }catch(e){ alert(e.message); }
});

/* ---------------- RESEARCH & GRANTS (faculty) ---------------- */
document.getElementById('grant-create-toggle').addEventListener('click', ()=> document.getElementById('grant-form').classList.toggle('open'));
document.getElementById('gr-submit').addEventListener('click', async ()=>{
  const title = document.getElementById('gr-title').value.trim(); if(!title) return;
  const description = document.getElementById('gr-desc').value.trim();
  try{
    await api('api/grants.php', {method:'POST', json:{action:'create', title, description}});
    document.getElementById('gr-title').value=''; document.getElementById('gr-desc').value='';
    document.getElementById('grant-form').classList.remove('open');
    await loadGrants(); renderMyGrants(); renderPendingGrants(); buildDashboard(CURRENT_USER.role);
  }catch(e){ alert(e.message); }
});

/* ---------------- SEARCH ---------------- */
let activeSearchCat = 'all';
async function runSearch(){
  const q = document.getElementById('search-input').value;
  const data = await api('api/search.php?q='+encodeURIComponent(q)+'&cat='+encodeURIComponent(activeSearchCat));
  render('search-grid', data.items);
}
document.getElementById('search-input').addEventListener('input', debounce(runSearch, 200));
document.getElementById('search-filter').addEventListener('click', (e)=>{
  if(!e.target.classList.contains('chip')) return;
  document.querySelectorAll('#search-filter .chip').forEach(c=>c.classList.remove('active'));
  e.target.classList.add('active'); activeSearchCat = e.target.dataset.cat; runSearch();
});
function debounce(fn, ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

/* ---------------- ALERT MODAL ---------------- */
const backdrop = document.getElementById('modal-backdrop');
let alertCycleIdx = 0;
document.getElementById('ticker-click').addEventListener('click', ()=>{
  if(!ACTIVE_ALERTS.length) return;
  const a = ACTIVE_ALERTS[alertCycleIdx % ACTIVE_ALERTS.length];
  alertCycleIdx++;
  const lower = a.message.toLowerCase();
  let tag=a.type||'Campus notice', tagClass='tag-club', showMap=false, desc='Posted by the admin office. Tap the ticker again for the next alert.';
  if(lower.includes('traffic')||lower.includes('road')){ tagClass='tag-admin'; showMap=true; desc='Expect delays near the affected route — plan an alternate path if possible.'; }
  else if(lower.includes('rain')||lower.includes('weather')){ tagClass='tag-academic'; desc='Carry an umbrella if you are commuting during this window.'; }
  else if(lower.includes('routine')||lower.includes('class')||lower.includes('exam')){ tagClass='tag-academic'; desc='Check the department notice board for full details.'; }
  document.getElementById('modal-tag').textContent = tag; document.getElementById('modal-tag').className='tag-pill '+tagClass;
  document.getElementById('modal-title').textContent = a.message; document.getElementById('modal-desc').textContent = desc;
  document.querySelector('.map-thumb').style.display = showMap ? 'block' : 'none';
  backdrop.classList.add('open');
});
document.getElementById('modal-close').addEventListener('click', ()=> backdrop.classList.remove('open'));
backdrop.addEventListener('click', (e)=>{ if(e.target===backdrop) backdrop.classList.remove('open'); });

/* ---------------- PROFILE ---------------- */
const photoInput = document.getElementById('profile-photo-input');
const avatarPreview = document.getElementById('profile-avatar-preview');
const avatarFallback = document.getElementById('profile-avatar-fallback');
let pendingAvatarFile = null;
photoInput.addEventListener('change', (e)=>{
  const file = e.target.files[0]; if(!file) return;
  pendingAvatarFile = file;
  const reader = new FileReader();
  reader.onload = (ev)=>{
    avatarPreview.src = ev.target.result; avatarPreview.style.display='flex'; avatarFallback.style.display='none';
    const tb = document.getElementById('topbar-avatar'); tb.style.backgroundImage = `url(${ev.target.result})`;
    tb.style.backgroundSize='cover'; tb.textContent='';
  };
  reader.readAsDataURL(file);
});
document.getElementById('profile-save-btn').addEventListener('click', async ()=>{
  const name = document.getElementById('profile-name').value.trim() || CURRENT_USER.name;
  const department = document.getElementById('profile-dept').value;
  const bio = document.getElementById('profile-bio').value.trim();
  const fd = new FormData();
  fd.append('name', name); fd.append('department', department); fd.append('bio', bio);
  if(pendingAvatarFile) fd.append('avatar', pendingAvatarFile);
  try{
    const res = await api('api/profile.php', {method:'POST', form:fd});
    CURRENT_USER = res.user;
    const roleLabel = document.getElementById('dash-role-label').textContent;
    document.getElementById('profile-head-name').textContent = name;
    document.getElementById('profile-head-sub').textContent = `${department} · ${roleLabel}`;
    document.getElementById('side-role-label').textContent = name;
    document.getElementById('dash-name').textContent = name;
    if(!pendingAvatarFile){ const initial = name.charAt(0).toUpperCase(); avatarFallback.textContent = initial; document.getElementById('topbar-avatar').textContent = initial; }
    const note = document.getElementById('profile-save-note'); note.classList.add('show'); setTimeout(()=>note.classList.remove('show'), 2500);
  }catch(e){ alert(e.message); }
});

/* ---------------- THEME TOGGLE ---------------- */
let isDark = false;
document.getElementById('theme-toggle').addEventListener('click', ()=>{
  isDark = !isDark;
  document.documentElement.setAttribute('data-theme', isDark?'dark':'light');
});

/* ---------------- LIVE COUNTER (ambient real-time feel) ---------------- */
setInterval(()=>{
  const el = document.getElementById('live-count'); if(!el) return;
  const cur = parseInt(el.textContent); const delta = Math.floor(Math.random()*7)-3;
  el.textContent = Math.max(120, cur+delta);
}, 3200);

/* ---------------- COMMAND PALETTE ---------------- */
const cmdkBackdrop = document.getElementById('cmdk-backdrop');
const cmdkInput = document.getElementById('cmdk-input');
const cmdkResults = document.getElementById('cmdk-results');
function cmdkIndex(){
  const role = CURRENT_USER ? CURRENT_USER.role : 'student';
  const navItems = NAV[role].map(([v,l])=>({label:l, tag:'Go to page', action:()=>goToView(v)}));
  const contentItems = [...NEWS, ...EVENTS, ...ACHIEVE, ...GRANTS].map(i=>({label:i.title, tag:i.category||i.cat, action:()=>goToView('search')}));
  return [...navItems, ...contentItems];
}
let cmdkSel = 0, cmdkFiltered = [];
function cmdkRender(q){
  const idx = cmdkIndex();
  if(q){
    cmdkFiltered = idx.filter(i=>i.label.toLowerCase().includes(q.toLowerCase()));
  } else {
    const navQuick = idx.filter(i=>i.tag==='Go to page').slice(0,3);
    const contentQuick = idx.filter(i=>i.tag!=='Go to page').slice(0,3);
    cmdkFiltered = [...navQuick, ...contentQuick];
  }
  cmdkSel = 0;
  cmdkResults.innerHTML = cmdkFiltered.length
    ? cmdkFiltered.map((i,n)=>`<div class="cmdk-item ${n===0?'sel':''}" data-i="${n}"><span>${i.label}</span><span class="cmdk-tag">${i.tag}</span></div>`).join('')
    : '<div class="empty-state">No matches.</div>';
}
function openCmdk(){ if(!CURRENT_USER) return; cmdkBackdrop.classList.add('open'); cmdkInput.value=''; cmdkRender(''); cmdkInput.focus(); }
function closeCmdk(){ cmdkBackdrop.classList.remove('open'); }
document.getElementById('cmdk-btn').addEventListener('click', openCmdk);
document.addEventListener('keydown', (e)=>{
  if((e.metaKey||e.ctrlKey) && e.key.toLowerCase()==='k'){ e.preventDefault(); openCmdk(); }
  if(e.key==='Escape') closeCmdk();
});
cmdkInput.addEventListener('input', (e)=> cmdkRender(e.target.value));
cmdkInput.addEventListener('keydown', (e)=>{
  if(e.key==='ArrowDown'){ e.preventDefault(); cmdkSel = Math.min(cmdkFiltered.length-1, cmdkSel+1); updateCmdkSel(); }
  if(e.key==='ArrowUp'){ e.preventDefault(); cmdkSel = Math.max(0, cmdkSel-1); updateCmdkSel(); }
  if(e.key==='Enter'){ if(cmdkFiltered[cmdkSel]){ cmdkFiltered[cmdkSel].action(); closeCmdk(); } }
});
function updateCmdkSel(){ document.querySelectorAll('.cmdk-item').forEach((el,n)=> el.classList.toggle('sel', n===cmdkSel)); }
cmdkResults.addEventListener('click', (e)=>{
  const item = e.target.closest('.cmdk-item'); if(!item) return;
  cmdkFiltered[+item.dataset.i].action(); closeCmdk();
});
cmdkBackdrop.addEventListener('click', (e)=>{ if(e.target===cmdkBackdrop) closeCmdk(); });

/* ---------------- BOOTSTRAP: check session on load ---------------- */
(async function bootstrap(){
  try{
    const data = await api('api/me.php');
    if(data.user){ await enterApp(data.user); }
  }catch(e){ /* not logged in — show login screen (default) */ }
})();
