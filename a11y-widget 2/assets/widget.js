/*!
 * A11y Widget – logic
 * Expose window.A11yWidget (registerFeature, get, set)
 */
(function(){
  const STORAGE_KEY = 'a11y-widget-prefs:v1';

  // -------- API publique --------
  const listeners = new Map(); // key -> Set<fct>

  const A11yAPI = {
    registerFeature(key, handler){
      if(!listeners.has(key)) listeners.set(key, new Set());
      listeners.get(key).add(handler);
      return () => listeners.get(key)?.delete(handler);
    },
    get(key){ return document.documentElement.dataset['a11y' + dashToCamel(key)] === 'on'; },
    set(key, value){ toggleFeature(key, !!value); persist(); }
  };
  window.A11yWidget = A11yAPI;

  function dashToCamel(s){
    return s.split('-').map((p,i)=> p.charAt(0).toUpperCase()+p.slice(1)).join('');
  }

  // ---------- Elements ----------
  const btn = document.getElementById('a11y-launcher');
  const overlay = document.getElementById('a11y-overlay');
  const closeBtn = document.getElementById('a11y-close');
  const closeBtn2 = document.getElementById('a11y-close2');
  const resetBtn = document.getElementById('a11y-reset');

  const featureInputs = Array.from(document.querySelectorAll('[data-feature]'));

  // ---------- Focus trap ----------
  let lastFocused = null;
  function openPanel(){
    lastFocused = document.activeElement;
    overlay.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
    btn.setAttribute('aria-expanded','true');
    // focus premier focusable
    const focusables = overlay.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    for (const el of focusables){ if(!el.hasAttribute('disabled') && el.offsetParent !== null){ el.focus(); break; } }
    overlay.addEventListener('keydown', trap, true);
  }
  function closePanel(){
    overlay.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
    btn.setAttribute('aria-expanded','false');
    overlay.removeEventListener('keydown', trap, true);
    if(lastFocused && lastFocused.focus) lastFocused.focus();
  }
  function trap(e){
    if(e.key === 'Escape'){ e.preventDefault(); closePanel(); return; }
    if(e.key !== 'Tab') return;
    const focusables = Array.from(overlay.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')).filter(el=>!el.hasAttribute('disabled') && el.offsetParent !== null);
    if(!focusables.length) return;
    const first = focusables[0];
    const last = focusables[focusables.length-1];
    if(e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
    else if(!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
  }

  // ---------- Persistance ----------
  function persist(){
    const data = {};
    featureInputs.forEach(input => { data[input.dataset.feature] = input.checked; });
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch(err){ /* ignore */ }
  }
  function restore(){
    try {
      const raw = localStorage.getItem(STORAGE_KEY); if(!raw) return;
      const data = JSON.parse(raw);
      for(const key in data){
        const val = !!data[key];
        const input = featureInputs.find(i=>i.dataset.feature===key);
        if(input){ input.checked = val; toggleFeature(key, val, {silent:true}); }
      }
    } catch(err){ /* ignore */ }
  }

  // ---------- Core toggle ----------
  function toggleFeature(key, on, opts={}){
    const attr = 'a11y' + dashToCamel(key);
    if(on) document.documentElement.dataset[attr] = 'on';
    else delete document.documentElement.dataset[attr];

    const ev = new CustomEvent('a11y:toggle', { detail: { key, on } });
    window.dispatchEvent(ev);

    const set = listeners.get(key);
    if(set) for(const fn of set) try { fn(on, key); } catch(e){}

    if(!opts.silent) persist();
  }

  // ---------- Wiring ----------
  if(btn){
    btn.addEventListener('click', openPanel);
  }
  if(overlay){
    overlay.addEventListener('click', (e)=>{ if(e.target === overlay) closePanel(); });
  }
  if(closeBtn){ closeBtn.addEventListener('click', closePanel); }
  if(closeBtn2){ closeBtn2.addEventListener('click', closePanel); }
  if(resetBtn){
    resetBtn.addEventListener('click', ()=>{
      featureInputs.forEach(i=>{ i.checked = false; toggleFeature(i.dataset.feature, false); });
      try { localStorage.removeItem(STORAGE_KEY); } catch(err){}
    });
  }
  featureInputs.forEach(input => {
    input.addEventListener('change', () => toggleFeature(input.dataset.feature, input.checked));
  });

  restore();

})();


// --- Robust event delegation (in case markup is injected after scripts) ---
document.addEventListener('click', function(e){
  const launcher = e.target.closest && e.target.closest('#a11y-launcher');
  const close1 = e.target.closest && e.target.closest('#a11y-close');
  const close2 = e.target.closest && e.target.closest('#a11y-close2');
  const overlayEl = document.getElementById('a11y-overlay');
  if(!overlayEl) return;
  function open(){ overlayEl.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
  function close(){ overlayEl.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
  if(launcher){ open(); }
  if(close1 || close2){ close(); }
});
