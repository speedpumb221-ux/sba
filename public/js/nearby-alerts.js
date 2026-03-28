/*
  nearby-alerts.js
  Vanilla JS smart nearby speed-bump alerts.

  Features:
  - watchPosition to follow user
  - fetch nearby bumps every POLL_INTERVAL or on movement
  - Haversine distance calculation
  - play audio per bump type, vibrate, and show toast
  - prevent duplicate alerts until user moves away > RESET_DISTANCE
  - lightweight, dependency-free, PWA-friendly

  Expects API: GET /api/nearby-speed-bumps?lat=...&lng=...
  and audio files at /audio/alert_confirmed.mp3, /audio/alert_predicted.mp3, or /audio/alert.mp3
*/
(function(){
  'use strict';

  // Config
  var POLL_INTERVAL = 5000; // ms
  var FETCH_RADIUS = 100; // meters to consider 'nearby'
  var RESET_DISTANCE = 50; // meters user must leave to allow re-alert
  var MOVEMENT_THRESHOLD = 10; // meters movement to trigger fetch earlier

  // State
  var lastFetchAt = 0;
  var lastFetchPos = null;
  var currentPos = null;
  var seen = new Map(); // bumpId -> { alertedAt, lastDistance }

  // Preload audio
  var audioConfirmed = new Audio('/audio/alert_confirmed.mp3');
  var audioPredicted = new Audio('/audio/alert_predicted.mp3');
  var audioFallback = new Audio('/audio/alert.mp3');
  [audioConfirmed, audioPredicted, audioFallback].forEach(function(a){ a.preload = 'auto'; a.load(); });

  // Create toast container
  var toastContainer = document.createElement('div');
  toastContainer.id = 'nearby-alerts-container';
  var tcStyle = toastContainer.style;
  tcStyle.position = 'fixed'; tcStyle.top = '12px'; tcStyle.left = '50%';
  tcStyle.transform = 'translateX(-50%)'; tcStyle.zIndex = 999999;
  tcStyle.display = 'flex'; tcStyle.flexDirection = 'column'; tcStyle.alignItems = 'center';
  document.addEventListener('DOMContentLoaded', function(){ document.body.appendChild(toastContainer); });

  // Inject toast styles (only once)
  (function injectToastStyles(){
    if (document.getElementById('nearby-alerts-styles')) return;
    var css = `
      .nearby-alert-toast{ display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.18); color:#fff; font-family: sans-serif; min-width:260px; max-width:92vw; direction:rtl; text-align:right; overflow:hidden; }

      .nearby-alert-toast__icon{ width:36px; height:36px; flex:0 0 36px; display:flex; align-items:center; justify-content:center; font-size:18px; }
      .nearby-alert-toast__content{ flex:1 1 auto; display:flex; flex-direction:column; }
      .nearby-alert-toast__title{ font-weight:700; margin-bottom:4px; }
      .nearby-alert-toast__lines{ font-size:13px; opacity:0.95; }

      .nearby-alert-toast--confirmed{ background:#d32f2f; }
      .nearby-alert-toast--predicted{ background:#ff9800; }

      @keyframes nearby-toast-in{ from{ transform: translateY(-8px) scale(0.98); opacity:0 } to{ transform: translateY(0) scale(1); opacity:1 } }
      @keyframes nearby-toast-out{ from{ opacity:1; transform: translateY(0) } to{ opacity:0; transform: translateY(-8px) } }

      .nearby-alert-toast{ animation: nearby-toast-in 320ms cubic-bezier(.2,.9,.2,1) both; }
      .nearby-alert-toast--hide{ animation: nearby-toast-out 220ms ease both; }
    `;
    var st = document.createElement('style'); st.id = 'nearby-alerts-styles'; st.appendChild(document.createTextNode(css));
    document.head.appendChild(st);
  })();
  // Haversine formula (meters)
  function haversine(lat1, lon1, lat2, lon2){
    function toRad(v){ return v * Math.PI / 180; }
    var R = 6371000; // meters
    var dLat = toRad(lat2 - lat1);
    var dLon = toRad(lon2 - lon1);
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
  }

  function formatDistance(m){
    if (m >= 1000) return (m/1000).toFixed(2) + ' km';
    return Math.round(m) + ' m';
  }

  function showToast(title, lines, type){
    var el = document.createElement('div');
    el.className = 'nearby-alert-toast';
    el.setAttribute('role','status');
    el.setAttribute('aria-live','polite');

    // content layout: icon + content + close
    var iconWrap = document.createElement('div'); iconWrap.className = 'nearby-alert-toast__icon';
    // default SVG warning icon
    iconWrap.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 21h22L12 2 1 21z" fill="currentColor"/><path d="M13 16h-2v2h2v-2zm0-6h-2v5h2V10z" fill="#fff"/></svg>';

    var content = document.createElement('div'); content.className = 'nearby-alert-toast__content';
    var titleEl = document.createElement('div'); titleEl.className = 'nearby-alert-toast__title'; titleEl.textContent = title;
    var linesWrap = document.createElement('div'); linesWrap.className = 'nearby-alert-toast__lines';
    lines.forEach(function(l){ var d = document.createElement('div'); d.textContent = l; linesWrap.appendChild(d); });
    content.appendChild(titleEl); content.appendChild(linesWrap);

    var closeBtn = document.createElement('button'); closeBtn.setAttribute('aria-label','إغلاق'); closeBtn.style.background='transparent'; closeBtn.style.border='none'; closeBtn.style.color='rgba(255,255,255,0.9)'; closeBtn.style.cursor='pointer'; closeBtn.style.fontSize='16px'; closeBtn.textContent = '✕';
    closeBtn.addEventListener('click', function(){ safeRemove(el); });

    el.appendChild(iconWrap);
    el.appendChild(content);
    el.appendChild(closeBtn);

    // apply type class for color
    try{ if(type === 'confirmed') el.classList.add('nearby-alert-toast--confirmed'); else if(type === 'predicted') el.classList.add('nearby-alert-toast--predicted'); }catch(e){}

    toastContainer.appendChild(el);

    // auto dismiss in 3s
    var to = setTimeout(function(){ safeRemove(el); }, 3000);
    // clear timeout when manually closed
    el._autoDismiss = to;
  }

  function safeRemove(node){
    try{
      if(!node) return;
      // cancel auto-dismiss
      if(node._autoDismiss) try{ clearTimeout(node._autoDismiss); }catch(e){}
      // if already hiding, skip
      if(node.classList && node.classList.contains('nearby-alert-toast--hide')) return;
      if(node.classList) {
        node.classList.add('nearby-alert-toast--hide');
        node.addEventListener('animationend', function(){ try{ node.remove(); }catch(e){ if(node.parentNode) node.parentNode.removeChild(node); } });
        // fallback removal
        setTimeout(function(){ try{ node.remove(); }catch(e){ if(node.parentNode) node.parentNode.removeChild(node); } }, 400);
      } else {
        try{ node.remove(); }catch(e){ if(node.parentNode) node.parentNode.removeChild(node); }
      }
    }catch(e){ try{ if(node && node.parentNode) node.parentNode.removeChild(node); }catch(_){} }
  }

  function playSoundForType(type){
    var a = audioFallback;
    if(type === 'confirmed') a = audioConfirmed;
    if(type === 'predicted') a = audioPredicted;
    // try play, ignore promise rejection
    try{ a.currentTime = 0; var p = a.play(); if(p && p.catch) p.catch(function(){ /* autoplay blocked */ }); }catch(e){}
  }

  function vibrate(){ try{ if(navigator.vibrate) navigator.vibrate([200,100,200]); }catch(e){} }

  // Core alert for a bump
  function alertForBump(bump, distance){
    var id = bump.id || (bump.latitude + '|' + bump.longitude);
    var type = (bump.type || bump.source || 'predicted').toString().toLowerCase();
    var numeric = Math.round(distance);

    // mark seen
    seen.set(id, { alertedAt: Date.now(), lastDistance: distance });

    // audio
    playSoundForType(type === 'confirmed' ? 'confirmed' : 'predicted');
    // vibrate
    vibrate();
    // toast
    var title = 'مطب قريب!';
    var lines = [ 'النوع: ' + (type === 'confirmed' ? 'مؤكد' : 'متوقع'), 'المسافة: ' + formatDistance(distance) ];
    showToast(title, lines, type === 'confirmed' ? 'confirmed' : 'predicted');

    // also try browser notification for visibility
    try{
      if('Notification' in window && Notification.permission === 'granted'){
        var n = new Notification('مطب قريب!', { body: (lines.join(' | ')) });
        setTimeout(function(){ try{ n.close(); }catch(e){} }, 5000);
      }
    }catch(e){}
  }

  function shouldAlert(bumpId, distance){
    var st = seen.get(bumpId);
    if(!st) return true;
    // if previously alerted, only alert again if user left more than RESET_DISTANCE
    if(st.lastDistance && distance > st.lastDistance + RESET_DISTANCE) {
      seen.delete(bumpId); return true;
    }
    return false;
  }

  // Fetch nearby bumps from server
  function fetchNearby(lat, lng){
    var now = Date.now();
    if(now - lastFetchAt < POLL_INTERVAL) return Promise.resolve(null);
    lastFetchAt = now; lastFetchPos = {lat: lat, lng: lng};
    var url = '/api/nearby-speed-bumps?lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng);
    return fetch(url, { credentials: 'same-origin' }).then(function(res){ if(!res.ok) throw new Error('fetch failed'); return res.json(); }).catch(function(){ return null; });
  }

  // Process bumps list
  function processBumps(bumps, lat, lng){
    if(!Array.isArray(bumps)) return;
    bumps.forEach(function(b){
      var d = haversine(lat, lng, parseFloat(b.latitude), parseFloat(b.longitude));
      var id = b.id || (b.latitude + '|' + b.longitude);
      // If within threshold and not recently alerted
      if(d <= FETCH_RADIUS){
        if(shouldAlert(id, d)){
          alertForBump(b, d);
        }
      }
      // If user has moved away sufficiently from a seen bump, allow re-alert (cleanup)
      var st = seen.get(id);
      if(st && d - st.lastDistance > RESET_DISTANCE){
        seen.delete(id);
      }
    });
  }

  // Throttled loop: ensure at least POLL_INTERVAL between server calls
  function maybeFetchAndProcess(pos){
    if(!pos) return;
    var now = Date.now();
    var lat = pos.coords.latitude, lng = pos.coords.longitude;
    var should = false;
    if(!lastFetchPos) should = true;
    else {
      var moved = haversine(lat, lng, lastFetchPos.lat, lastFetchPos.lng);
      if(moved >= MOVEMENT_THRESHOLD) should = true;
      if(now - lastFetchAt >= POLL_INTERVAL) should = true;
    }
    if(!should) return;
    fetchNearby(lat, lng).then(function(data){ if(!data) return; processBumps(data, lat, lng); });
  }

  // Geolocation watcher
  var watchId = null;
  function startWatching(){
    if(!('geolocation' in navigator)){
      console.warn('Geolocation not supported'); return;
    }
    try{
      watchId = navigator.geolocation.watchPosition(function(pos){
        currentPos = pos;
        maybeFetchAndProcess(pos);
      }, function(err){
        console.warn('watchPosition error', err); // do not spam
      }, { enableHighAccuracy: true, maximumAge: 1000, timeout: 10000 });
    }catch(e){ console.warn('geolocation watch failed', e); }
  }

  // Request notification permission non-blocking
  try{ if('Notification' in window && Notification.permission !== 'granted') Notification.requestPermission().catch(function(){}); }catch(e){}

  // Start when DOM ready and when user interacts (to improve audio autoplay reliability)
  function init(){
    if(!('geolocation' in navigator)) return;
    startWatching();
    // ensure we perform an initial fetch if we have a position already
    if(currentPos) maybeFetchAndProcess(currentPos);
  }

  // Allow manual start via window.NearbyAlerts.init()
  window.NearbyAlerts = { init: init, _seen: seen };

  // Auto init after short delay (gives page time and permission prompts)
  document.addEventListener('visibilitychange', function(){ if(document.visibilityState === 'visible') { init(); } });
  window.addEventListener('load', function(){ setTimeout(init, 800); });

})();
