/*
  nearby-alerts-fixed.js
  Fixed variant of nearby-alerts.js with correct multiline CSS using template literals.
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

  // Preload audio if files exist (avoid 404 noise). Fallback to oscillator when none available.
  var audioConfirmed = null;
  var audioPredicted = null;
  var audioFallback = null;
  var useOscillator = false;

  function tryLoadAudio(path){
    return fetch(path, { method: 'HEAD', credentials: 'same-origin' }).then(function(res){
      return res && res.ok ? path : null;
    }).catch(function(){ return null; });
  }

  Promise.all([
    tryLoadAudio('/audio/alert_confirmed.mp3'),
    tryLoadAudio('/audio/alert_predicted.mp3'),
    tryLoadAudio('/audio/alert.mp3')
  ]).then(function(results){
    var c = results[0], p = results[1], f = results[2];
    if(!c && !p && !f){
      useOscillator = true; // nothing available, use oscillator
      return;
    }
    try{
      if(c){ audioConfirmed = new Audio(c); audioConfirmed.preload = 'auto'; }
      if(p){ audioPredicted = new Audio(p); audioPredicted.preload = 'auto'; }
      if(f){ audioFallback = new Audio(f); audioFallback.preload = 'auto'; }
      [audioConfirmed, audioPredicted, audioFallback].forEach(function(a){ if(!a) return; a.load(); a.addEventListener('error', function(){ useOscillator = true; }); });
    }catch(e){ useOscillator = true; }
  }).catch(function(){ useOscillator = true; });

  // Create toast container
  var toastContainer = document.createElement('div');
  toastContainer.id = 'nearby-alerts-container';
  var tcStyle = toastContainer.style;
  tcStyle.position = 'fixed'; tcStyle.top = '12px'; tcStyle.left = '50%';
  tcStyle.transform = 'translateX(-50%)'; tcStyle.zIndex = 999999;
  tcStyle.display = 'flex'; tcStyle.flexDirection = 'column'; tcStyle.alignItems = 'center';
  document.addEventListener('DOMContentLoaded', function(){ document.body.appendChild(toastContainer); });

  // Inject toast styles (only once) - using template literal
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

    try{ if(type === 'confirmed') el.classList.add('nearby-alert-toast--confirmed'); else if(type === 'predicted') el.classList.add('nearby-alert-toast--predicted'); }catch(e){}

    toastContainer.appendChild(el);

    // auto dismiss in 3s
    var to = setTimeout(function(){ safeRemove(el); }, 3000);
    el._autoDismiss = to;
  }

  function safeRemove(node){
    try{
      if(!node) return;
      if(node._autoDismiss) try{ clearTimeout(node._autoDismiss); }catch(e){}
      if(node.classList && node.classList.contains('nearby-alert-toast--hide')) return;
      if(node.classList) {
        node.classList.add('nearby-alert-toast--hide');
        node.addEventListener('animationend', function(){ try{ node.remove(); }catch(e){ if(node.parentNode) node.parentNode.removeChild(node); } });
        setTimeout(function(){ try{ node.remove(); }catch(e){ if(node.parentNode) node.parentNode.removeChild(node); } }, 400);
      } else {
        try{ node.remove(); }catch(e){ if(node.parentNode) node.parentNode.removeChild(node); }
      }
    }catch(e){ try{ if(node && node.parentNode) node.parentNode.removeChild(node); }catch(_){} }
  }

  function playSoundForType(type){
    if(useOscillator){ playOscillator(type); return; }
    var a = audioFallback;
    if(type === 'confirmed') a = audioConfirmed;
    if(type === 'predicted') a = audioPredicted;
    try{ a.currentTime = 0; var p = a.play(); if(p && p.catch) p.catch(function(){}); }catch(e){ /* ignore */ }
  }

  function playOscillator(type){
    try{
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var o = ctx.createOscillator();
      var g = ctx.createGain();
      var freq = (type === 'confirmed') ? 880 : (type === 'predicted') ? 660 : 440;
      o.type = 'sine'; o.frequency.value = freq;
      g.gain.value = 0.001;
      o.connect(g); g.connect(ctx.destination);
      o.start();
      g.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.01);
      g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.45);
      setTimeout(function(){ try{ o.stop(); ctx.close(); }catch(e){} }, 600);
    }catch(e){ /* ignore */ }
  }

  function vibrate(){ try{ if(navigator.vibrate) navigator.vibrate([200,100,200]); }catch(e){} }

  function alertForBump(bump, distance){
    var id = bump.id || (bump.latitude + '|' + bump.longitude);
    var type = (bump.type || bump.source || 'predicted').toString().toLowerCase();
    var numeric = Math.round(distance);
    seen.set(id, { alertedAt: Date.now(), lastDistance: distance });
    playSoundForType(type === 'confirmed' ? 'confirmed' : 'predicted');
    vibrate();
    var title = 'مطب قريب!';
    var lines = [ 'النوع: ' + (type === 'confirmed' ? 'مؤكد' : 'متوقع'), 'المسافة: ' + formatDistance(distance) ];
    showToast(title, lines, type === 'confirmed' ? 'confirmed' : 'predicted');
    try{ if('Notification' in window && Notification.permission === 'granted'){ var n = new Notification('مطب قريب!', { body: (lines.join(' | ')) }); setTimeout(function(){ try{ n.close(); }catch(e){} }, 5000); } }catch(e){}
  }

  function shouldAlert(bumpId, distance){
    var st = seen.get(bumpId);
    if(!st) return true;
    if(st.lastDistance && distance > st.lastDistance + RESET_DISTANCE) { seen.delete(bumpId); return true; }
    return false;
  }

  function fetchNearby(lat, lng){
    var now = Date.now();
    if(now - lastFetchAt < POLL_INTERVAL) return Promise.resolve(null);
    lastFetchAt = now; lastFetchPos = {lat: lat, lng: lng};
    var url = '/api/bumps/nearby';
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ latitude: lat, longitude: lng, radius: FETCH_RADIUS })
    }).then(function(res){
      if(!res.ok) throw new Error('fetch failed ' + res.status);
      return res.json();
    }).then(function(data){
      // controller returns { success: true, nearby: [...] }
      if(!data) return null;
      return data.nearby || null;
    }).catch(function(){ return null; });
  }

  function processBumps(bumps, lat, lng){
    if(!Array.isArray(bumps)) return;
    bumps.forEach(function(b){
      var d = haversine(lat, lng, parseFloat(b.latitude), parseFloat(b.longitude));
      var id = b.id || (b.latitude + '|' + b.longitude);
      if(d <= FETCH_RADIUS){ if(shouldAlert(id, d)){ alertForBump(b, d); } }
      var st = seen.get(id);
      if(st && d - st.lastDistance > RESET_DISTANCE){ seen.delete(id); }
    });
  }

  function maybeFetchAndProcess(pos){
    if(!pos) return;
    var now = Date.now();
    var lat = pos.coords.latitude, lng = pos.coords.longitude;
    var should = false;
    if(!lastFetchPos) should = true; else { var moved = haversine(lat, lng, lastFetchPos.lat, lastFetchPos.lng); if(moved >= MOVEMENT_THRESHOLD) should = true; if(now - lastFetchAt >= POLL_INTERVAL) should = true; }
    if(!should) return;
    fetchNearby(lat, lng).then(function(data){ if(!data) return; processBumps(data, lat, lng); });
  }

  var watchId = null;
  function startWatching(){
    if(!('geolocation' in navigator)){ console.warn('Geolocation not supported'); showToast('الموقع غير متاح', ['المتصفح لا يدعم خدمات الموقع'], 'predicted'); return; }
    try{
      watchId = navigator.geolocation.watchPosition(function(pos){ currentPos = pos; maybeFetchAndProcess(pos); }, function(err){
        console.warn('watchPosition error', err);
        // handle common geolocation errors
        try{
          switch(err.code){
            case 1: // PERMISSION_DENIED
              showToast('الموقع معطل', ['يرجى تمكين خدمات الموقع في إعدادات المتصفح'], 'predicted');
              if(watchId !== null) try{ navigator.geolocation.clearWatch(watchId); }catch(e){}
              break;
            case 2: // POSITION_UNAVAILABLE
              showToast('تعذّر الحصول على الموقع', ['الموقع غير متوفر حالياً'], 'predicted');
              break;
            case 3: // TIMEOUT
              showToast('انتهى وقت الحصول على الموقع', ['حاول تقليل دقة الموقع أو تحقق من الإشارة'], 'predicted');
              break;
            default:
              showToast('خطأ في الموقع', ['تحقق من إعدادات الموقع أو أعد المحاولة'], 'predicted');
          }
        }catch(e){ /* ignore */ }
      }, { enableHighAccuracy: true, maximumAge: 1000, timeout: 10000 });
    }catch(e){ console.warn('geolocation watch failed', e); }
  }

  try{ if('Notification' in window && Notification.permission !== 'granted') Notification.requestPermission().catch(function(){}); }catch(e){}

  function init(){
    if(!('geolocation' in navigator)) return;

    // Check permission state when possible to provide helpful guidance instead of silent failure
    if(navigator.permissions && navigator.permissions.query){
      try{
        navigator.permissions.query({ name: 'geolocation' }).then(function(p){
          if(p.state === 'denied'){
            // show persistent retry toast
            showToast('الموقع معطل', ['تم رفض الوصول للموقع في إعدادات المتصفح. اضغط إعادة المحاولة لإعادة الطلب.'], 'predicted');
            var retryEl = document.createElement('div');
            retryEl.className = 'nearby-alert-toast';
            retryEl.style.marginTop = '8px';
            var content = document.createElement('div'); content.className = 'nearby-alert-toast__content';
            var title = document.createElement('div'); title.className = 'nearby-alert-toast__title'; title.textContent = 'تم رفض الموقع';
            var btn = document.createElement('button'); btn.textContent = 'إعادة المحاولة'; btn.style.marginTop = '8px'; btn.style.padding = '6px 10px'; btn.style.borderRadius = '6px'; btn.style.border = 'none'; btn.style.cursor = 'pointer';
            btn.addEventListener('click', function(){
              try{
                navigator.geolocation.getCurrentPosition(function(p){
                  try{ if(retryEl && retryEl.parentNode) retryEl.parentNode.removeChild(retryEl); }catch(e){}
                  currentPos = p; maybeFetchAndProcess(p); startWatching();
                }, function(err){ showToast('طلب الموقع فشل', [err && err.message ? err.message : 'فشل الحصول على الموقع'], 'predicted'); }, { enableHighAccuracy: true, timeout: 8000 });
              }catch(e){ showToast('فشل إعادة المحاولة', ['تعذّر الوصول إلى واجهة الموقع'], 'predicted'); }
            });
            content.appendChild(title); content.appendChild(btn); retryEl.appendChild(content);
            document.addEventListener('DOMContentLoaded', function(){ toastContainer.appendChild(retryEl); });
          } else {
            startWatching();
          }
        }).catch(function(){ startWatching(); });
      }catch(e){ startWatching(); }
    } else {
      startWatching();
    }

    if(currentPos) maybeFetchAndProcess(currentPos);
  }


  window.NearbyAlerts = { init: init, _seen: seen };
  document.addEventListener('visibilitychange', function(){ if(document.visibilityState === 'visible') { init(); } });
  window.addEventListener('load', function(){ setTimeout(init, 800); });

})();
