<?php
// Campus Pulse — main entry point.
// If the user is already logged in (has a PHP session), we still serve
// this same page — app.js checks /api/me.php on load and shows the
// correct screen (login vs app shell) automatically.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Campus Pulse — UIU</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ================= LANDING ================= -->
<div id="landing-screen">
  <div class="login-visual">
    <div class="lv-corner tl"></div><div class="lv-corner tr"></div><div class="lv-corner bl"></div><div class="lv-corner br"></div>
    <div class="crest">
      <div class="crest-ring"><span>CP</span></div>
      <div class="crest-est">CAMPUS PULSE · EST. UIU</div>
    </div>
    <div class="quote">
      <span class="quote-mark">&ldquo;</span>
      <h2>Everything happening at UIU, in one live feed.</h2>
      <div class="gold-rule"></div>
      <p>News, events, achievements, research grants and location-based alerts — kept in one place, for students, faculty and administrators alike.</p>
    </div>
  </div>
  <div class="login-side">
    <div class="login-card" style="text-align:center;">
      <div class="mobile-brand" style="justify-content:center;"><span class="ring-dot"></span><span>Campus Pulse</span></div>
      <h1>Welcome to Campus Pulse</h1>
      <p class="tag">UIU's live feed for news, events, resources, research grants and campus alerts — for students, faculty and admins.</p>
      <button class="btn-primary" id="landing-login-btn">Log in</button>
      <button class="btn-secondary" id="landing-signup-btn">Create an account</button>
    </div>
  </div>
</div>

<!-- ================= LOGIN ================= -->
<div id="login-screen" style="display:none;">
  <div class="login-visual">
    <div class="lv-corner tl"></div><div class="lv-corner tr"></div><div class="lv-corner bl"></div><div class="lv-corner br"></div>
    <div class="crest">
      <div class="crest-ring"><span>CP</span></div>
      <div class="crest-est">CAMPUS PULSE · EST. UIU</div>
    </div>
    <div class="quote">
      <span class="quote-mark">&ldquo;</span>
      <h2>Everything happening at UIU, in one live feed.</h2>
      <div class="gold-rule"></div>
      <p>News, events, achievements, research grants and location-based alerts — kept in one place, for students, faculty and administrators alike.</p>
    </div>
  </div>
  <div class="login-side">
    <div class="login-card">
      <button type="button" class="back-link" id="login-back-btn">← Back</button>
      <div class="mobile-brand"><span class="ring-dot"></span><span>Campus Pulse</span></div>
      <h1>Welcome back</h1>
      <p class="tag">Sign in to your UIU account</p>
      <div class="field"><label>UIU email</label><input type="text" id="login-email" value="shad@uiu.ac.bd" placeholder="you@uiu.ac.bd"></div>
      <div class="field"><label>Password</label><input type="password" id="login-pass" placeholder="password123"></div>
      <div class="field">
        <label>Sign in as</label>
        <div class="role-pick" id="role-pick">
          <button type="button" class="active" data-role="student">Student</button>
          <button type="button" data-role="faculty">Faculty</button>
          <button type="button" data-role="admin">Admin</button>
        </div>
      </div>
      <button class="btn-primary" id="login-btn">Sign in</button>
      <p id="login-error" style="display:none;color:var(--clay);font-size:12.5px;margin:10px 0 0;"></p>
      <p style="color:var(--muted-soft);font-size:11.5px;margin:14px 0 0;line-height:1.6;">Demo accounts (password: <code>password123</code>): shad@uiu.ac.bd (student) · farhana@uiu.ac.bd (faculty) · admin@uiu.ac.bd (admin)</p>
      <p style="font-size:12.5px;margin:16px 0 0;">New here? <button type="button" class="link-btn" id="go-signup-link" style="padding:0;">Create an account</button></p>
    </div>
  </div>
</div>

<!-- ================= SIGN UP ================= -->
<div id="signup-screen" style="display:none;">
  <div class="login-visual">
    <div class="lv-corner tl"></div><div class="lv-corner tr"></div><div class="lv-corner bl"></div><div class="lv-corner br"></div>
    <div class="crest">
      <div class="crest-ring"><span>CP</span></div>
      <div class="crest-est">CAMPUS PULSE · EST. UIU</div>
    </div>
    <div class="quote">
      <span class="quote-mark">&ldquo;</span>
      <h2>Everything happening at UIU, in one live feed.</h2>
      <div class="gold-rule"></div>
      <p>News, events, achievements, research grants and location-based alerts — kept in one place, for students, faculty and administrators alike.</p>
    </div>
  </div>
  <div class="login-side">
    <div class="login-card">
      <button type="button" class="back-link" id="signup-back-btn">← Back</button>
      <div class="mobile-brand"><span class="ring-dot"></span><span>Campus Pulse</span></div>
      <h1>Create your account</h1>
      <p class="tag">Sign up with your UIU email</p>
      <div class="field"><label>Full name</label><input type="text" id="signup-name" placeholder="e.g. Shad Hossain"></div>
      <div class="field"><label>UIU email</label><input type="text" id="signup-email" placeholder="you@uiu.ac.bd"></div>
      <div class="field"><label>Password</label><input type="password" id="signup-pass" placeholder="At least 6 characters"></div>
      <div class="field"><label>Department</label>
        <select id="signup-dept"><option>CSE</option><option>EEE</option><option>BBA</option><option>Pharmacy</option><option>Civil</option></select>
      </div>
      <div class="field">
        <label>I am a</label>
        <div class="role-pick" id="signup-role-pick">
          <button type="button" class="active" data-role="student">Student</button>
          <button type="button" data-role="faculty">Faculty</button>
          <button type="button" data-role="admin">Admin</button>
        </div>
      </div>
      <button class="btn-primary" id="signup-btn">Sign up</button>
      <p id="signup-error" style="display:none;color:var(--clay);font-size:12.5px;margin:10px 0 0;"></p>
      <p style="font-size:12.5px;margin:16px 0 0;">Already have an account? <button type="button" class="link-btn" id="go-login-link" style="padding:0;">Log in</button></p>
    </div>
  </div>
</div>

<!-- ================= APP SHELL ================= -->
<div id="app-shell">
  <div class="mobile-bar">
    <button id="mobile-menu-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
    <span>Campus Pulse</span>
  </div>
  <div class="shell">
    <aside class="sidebar" id="sidebar-el">
      <div class="side-brand"><span class="ring-dot"></span><span>Campus Pulse</span></div>
      <span class="role-tag" id="role-tag">Student</span>
      <nav class="side-nav" id="side-nav"></nav>
      <div class="side-foot">Signed in as <b id="side-role-label">Shad</b><br><button id="logout-btn">Sign out</button></div>
    </aside>

    <main class="main">
      <div class="topbar">
        <h1 id="view-title">Home feed</h1>
        <div class="topbar-actions">
          <button class="icon-btn" id="cmdk-btn" title="Quick search (Ctrl+K)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v2m14-4h-2a2 2 0 0 0-2 2v2M5 15v2a2 2 0 0 0 2 2h2m8 0h2a2 2 0 0 0 2-2v-2"/></svg>
          </button>
          <button class="icon-btn theme-toggle" id="theme-toggle" title="Toggle theme">
            <svg id="theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4.5"/><path d="M12 2.5v3M12 18.5v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2.5 12h3M18.5 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg>
          </button>
          <div class="bell-wrap">
            <button class="icon-btn" id="bell-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
              <span class="badge" id="bell-badge">3</span>
            </button>
            <div class="bell-menu" id="bell-menu">
              <div class="bell-item" data-nid="1"><b>Class suspended — CSE building</b><div class="t">Water supply maintenance · 10m ago</div></div>
              <div class="bell-item" data-nid="2"><b>New event: Robotics Fest registration open</b><div class="t">1h ago</div></div>
              <div class="bell-item" data-nid="3"><b>Research grant deadline in 3 days</b><div class="t">3h ago</div></div>
              <button type="button" class="bell-mark-all" id="bell-mark-all">Mark all as read</button>
            </div>
          </div>
          <button class="avatar" id="topbar-avatar" data-goto="profile" title="View profile">S</button>
        </div>
      </div>

      <span class="status-pill normal" id="status-pill"><span class="sdot"></span><span id="status-pill-text">Campus status: Normal</span></span>
      <br>

      <div class="ticker">
        <span class="tk-label">LIVE</span>
        <div class="ticker-track" id="ticker-click">
          <div class="ticker-track-inner" id="ticker-inner">
            <span class="ticker-item" id="ticker-text">Heavy traffic reported on Satarkul Road near campus gate · Light rain expected this evening, carry umbrella · Mid-term routine published for Summer 2026 · Robotics Fest registration closes Thursday</span>
            <span class="ticker-item" id="ticker-text-2" aria-hidden="true">Heavy traffic reported on Satarkul Road near campus gate · Light rain expected this evening, carry umbrella · Mid-term routine published for Summer 2026 · Robotics Fest registration closes Thursday</span>
          </div>
        </div>
      </div>

      <!-- ---- HOME ---- -->
      <section class="view active" id="view-home">
        <div class="live-counter">
          <span class="lc-dot"></span>
          <span class="num" id="live-count">482</span>
          <span class="lbl">students active on campus right now</span>
        </div>
        <div class="preview-strip">
          <div class="l"><b>UIU Robotics Fest 2026</b><span>Aug 3 · Auditorium 2 · 340 registered</span></div>
          <button class="link-btn" data-goto="events">View events →</button>
        </div>
        <div class="section-title">Campus news</div>
        <div class="grid-cards" id="home-news-grid"></div>
      </section>

      <!-- ---- EVENTS ---- -->
      <section class="view" id="view-events">
        <div class="section-head">
          <div class="section-title" style="margin:0;">Browse events</div>
          <button class="action-btn" id="event-create-toggle" style="display:none;">+ Create event</button>
        </div>
        <div class="form-card" id="event-form">
          <div class="form-row2">
            <div class="field"><label>Event title</label><input type="text" id="ev-title" placeholder="e.g. AI Workshop"></div>
            <div class="field"><label>Category</label><select id="ev-cat"><option>Academic</option><option>Club</option><option>Competition</option></select></div>
          </div>
          <div class="field"><label>Date &amp; venue</label><input type="text" id="ev-meta" placeholder="e.g. Aug 12 · Room 501"></div>
          <button class="btn-primary" style="width:auto;padding:10px 20px;" id="ev-submit">Submit event</button>
        </div>
        <div class="filter-row" id="events-filter">
          <button class="chip active" data-cat="all">All</button>
          <button class="chip" data-cat="Academic">Academic</button>
          <button class="chip" data-cat="Club">Club</button>
          <button class="chip" data-cat="Competition">Competition</button>
        </div>
        <div class="grid-cards" id="events-grid"></div>

        <div id="events-pending-block" style="display:none;">
          <div class="section-title">Pending approval</div>
          <div class="grid-cards" id="events-pending-grid"></div>
        </div>
      </section>

      <!-- ---- RESOURCES (study hub) ---- -->
      <section class="view" id="view-resources">
        <div class="section-title">Quick links</div>
        <div class="qlink-grid" id="qlink-grid"></div>

        <div class="section-head">
          <div class="filter-row" id="resource-tabs" style="margin:0;">
            <button class="chip active" data-kind="notes">Notes</button>
            <button class="chip" data-kind="qbank">Question bank</button>
          </div>
          <button class="action-btn" id="resource-upload-toggle">+ Upload</button>
        </div>

        <div class="form-card" id="resource-form">
          <div class="form-row2">
            <div class="field"><label>Course code</label><input type="text" id="res-course" placeholder="e.g. CSE 4165"></div>
            <div class="field"><label>Type</label><select id="res-kind"><option value="notes">Notes</option><option value="qbank">Question bank</option></select></div>
          </div>
          <div class="field"><label>Title</label><input type="text" id="res-title" placeholder="e.g. Midterm question paper, Summer 2025"></div>
          <div class="field"><label>Attach file</label><input type="file" id="res-file"></div>
          <button class="btn-primary" style="width:auto;padding:10px 20px;" id="res-submit">Submit</button>
        </div>

        <div class="grid-cards" id="resources-grid"></div>

        <div id="resources-pending-block" style="display:none;">
          <div class="section-title">Pending uploads (needs review)</div>
          <div class="grid-cards" id="resources-pending-grid"></div>
        </div>
      </section>

      <!-- ---- RESEARCH & GRANTS (faculty) ---- -->
      <section class="view" id="view-grants">
        <div class="section-head">
          <div class="section-title" style="margin:0;">My submissions</div>
          <button class="action-btn" id="grant-create-toggle">+ Submit new</button>
        </div>
        <div class="form-card" id="grant-form">
          <div class="field"><label>Title</label><input type="text" id="gr-title" placeholder="e.g. Applied ML for crop yield prediction"></div>
          <div class="field"><label>Short description</label><textarea id="gr-desc" placeholder="1-2 lines on the grant or research work"></textarea></div>
          <button class="btn-primary" style="width:auto;padding:10px 20px;" id="gr-submit">Submit for review</button>
        </div>
        <div class="grid-cards" id="my-grants-grid"></div>
      </section>

      <!-- ---- ACHIEVEMENTS ---- -->
      <section class="view" id="view-achievements">
        <div class="section-title">Student &amp; faculty spotlight</div>
        <div class="grid-cards" id="achieve-grid"></div>
        <div class="section-title">Research grants</div>
        <div class="grid-cards" id="grant-grid"></div>
      </section>

      <!-- ---- MANAGE GRANTS (admin) ---- -->
      <section class="view" id="view-grants-admin">
        <div class="section-title">Pending grant submissions</div>
        <div class="grid-cards" id="grants-pending-grid"></div>
        <div class="section-title">Approved research grants</div>
        <div class="grid-cards" id="grant-grid-admin"></div>
      </section>

      <!-- ---- MANAGE ALERTS (admin) ---- -->
      <section class="view" id="view-alerts">
        <div class="section-title">Campus status</div>
        <div class="form-card open" style="margin-bottom:22px;">
          <div class="field"><label>Set campus-wide status</label>
            <select id="admin-status-select">
              <option value="normal">Normal</option>
              <option value="alert">Alert — minor disruption</option>
              <option value="critical">Critical — class suspended / hazard</option>
            </select>
          </div>
          <button class="btn-primary" style="width:auto;padding:10px 20px;" id="admin-status-btn">Update status</button>
        </div>
        <div class="section-title">Post a new alert</div>
        <div class="form-card open" style="margin-bottom:22px;">
          <div class="form-row2">
            <div class="field"><label>Alert title</label><input type="text" id="admin-alert-title" placeholder="e.g. Road closed near Gate 2"></div>
            <div class="field"><label>Type</label><select id="admin-alert-type"><option>Traffic</option><option>Weather</option><option>Campus notice</option></select></div>
          </div>
          <button class="btn-primary" style="width:auto;padding:10px 20px;" id="admin-post-btn">Post to ticker</button>
        </div>
        <div class="section-title">Active alerts</div>
        <div id="active-alerts-list"></div>
      </section>

      <!-- ---- USER DIRECTORY (admin) ---- -->
      <section class="view" id="view-directory">
        <div class="search-bar"><input type="text" id="directory-search" placeholder="Search by name or department…"></div>
        <table class="directory">
          <thead><tr><th>Name</th><th>Department</th><th>Role</th></tr></thead>
          <tbody id="directory-body"></tbody>
        </table>
      </section>

      <!-- ---- SEARCH ---- -->
      <section class="view" id="view-search">
        <div class="search-bar"><input type="text" id="search-input" placeholder="Search news, events, achievements…"></div>
        <div class="filter-row" id="search-filter">
          <button class="chip active" data-cat="all">All categories</button>
          <button class="chip" data-cat="Academic">Academic</button>
          <button class="chip" data-cat="Admin">Admin</button>
          <button class="chip" data-cat="Club">Club</button>
          <button class="chip" data-cat="Competition">Competition</button>
        </div>
        <div class="grid-cards" id="search-grid"></div>
      </section>

      <!-- ---- DASHBOARD ---- -->
      <section class="view" id="view-dashboard">
        <div class="dash-grid">
          <div id="dashboard-left"></div>
          <div>
            <div class="section-title">Profile</div>
            <div class="stat-box">
              <p style="margin:0 0 8px;font-size:13px;"><b id="dash-name">Shad</b><br><span style="color:var(--muted);font-size:12px;">CSE · Signed in as <span id="dash-role-label">Student</span></span></p>
              <p style="margin:0;font-size:12px;color:var(--muted);">Manage notification radius, saved filters, and alert preferences from your profile.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ---- PROFILE ---- -->
      <section class="view" id="view-profile">
        <div class="dash-grid">
          <div class="stat-box">
            <div class="profile-head">
              <img class="avatar-lg" id="profile-avatar-preview" style="display:none;">
              <div class="avatar-lg" id="profile-avatar-fallback">S</div>
              <div class="info">
                <b id="profile-head-name">Shad</b>
                <span id="profile-head-sub">CSE · Student</span>
                <label class="upload-btn" for="profile-photo-input">Change photo</label>
                <input type="file" id="profile-photo-input" accept="image/*">
              </div>
            </div>
            <div class="field"><label>Full name</label><input type="text" id="profile-name" value="Shad"></div>
            <div class="field"><label>Department</label>
              <select id="profile-dept"><option>CSE</option><option>EEE</option><option>BBA</option><option>Pharmacy</option><option>Civil</option></select>
            </div>
            <div class="field"><label>UIU email</label><input type="text" id="profile-email" value="shad@uiu.ac.bd" disabled></div>
            <div class="field"><label>Short bio</label><textarea id="profile-bio">CSE student, building civic-tech side projects.</textarea></div>
            <button class="btn-primary" id="profile-save-btn" style="width:auto;padding:10px 22px;">Save changes</button>
            <div class="save-note" id="profile-save-note">Profile updated.</div>
          </div>
          <div>
            <div class="section-title">Notification settings</div>
            <div class="stat-box">
              <div class="saved-item"><span>Traffic alerts</span><label class="switch"><input type="checkbox" class="notif-toggle" data-pref="traffic" checked><span class="switch-slider"></span></label></div>
              <div class="saved-item"><span>Weather alerts</span><label class="switch"><input type="checkbox" class="notif-toggle" data-pref="weather" checked><span class="switch-slider"></span></label></div>
              <div class="saved-item"><span>Event reminders</span><label class="switch"><input type="checkbox" class="notif-toggle" data-pref="events" checked><span class="switch-slider"></span></label></div>
              <div class="saved-item"><span>Research &amp; grants</span><label class="switch"><input type="checkbox" class="notif-toggle" data-pref="grants"><span class="switch-slider"></span></label></div>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>
</div>

<div class="cmdk-backdrop" id="cmdk-backdrop">
  <div class="cmdk-box">
    <input type="text" id="cmdk-input" placeholder="Jump to a page or search everything…" autocomplete="off">
    <div class="cmdk-results" id="cmdk-results"></div>
    <div class="cmdk-hint">Searches pages, news, events &amp; grants at once · ↑↓ Enter · Esc to close</div>
  </div>
</div>

<div class="modal-backdrop" id="modal-backdrop">
  <div class="modal-box">
    <button class="close-x" id="modal-close">✕</button>
    <span class="tag-pill tag-admin" id="modal-tag">Traffic</span>
    <h3 id="modal-title" style="margin:8px 0 6px;">Alert title</h3>
    <p id="modal-desc" style="font-size:13px;color:var(--muted);margin:0;">Description</p>
    <div class="map-thumb"><span class="pin">📍</span></div>
    <p id="modal-meta" style="font-size:11.5px;color:var(--muted-soft);margin:10px 0 0;">Reported 12 minutes ago · Near campus gate</p>
    <div id="modal-extra"></div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>