/*!
 * A11y Widget – logic
 * Expose window.A11yWidget (registerFeature, get, set)
 */
(function(){
  const STORAGE_KEY = 'a11y-widget-prefs:v1';
  const LAUNCHER_POS_KEY = 'a11y-widget-launcher-pos:v1';

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

  function ensureFeatureState(slug){
    if(!slug) return;
    if(!featureState.has(slug)){
      featureState.set(slug, false);
    }
  }

  function seedFeatureState(){
    if(!Array.isArray(sectionsData)) return;
    sectionsData.forEach(section => {
      if(!section || !Array.isArray(section.features)) return;
      section.features.forEach(feature => {
        if(!feature) return;
        const slug = typeof feature.slug === 'string' ? feature.slug : '';
        if(slug) ensureFeatureState(slug);
      });
    });
  }

  function updateTabState(index){
    if(!tabs.length) return null;
    let activeTab = null;
    tabs.forEach((tab, i) => {
      const isActive = i === index;
      tab.classList.toggle('is-active', isActive);
      tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      tab.setAttribute('tabindex', isActive ? '0' : '-1');
      if(isActive){ activeTab = tab; }
    });
    return activeTab;
  }

  function createFeatureCard(feature){
    if(!feature || !featureGrid) return null;
    const card = featureTemplate && featureTemplate.content && featureTemplate.content.firstElementChild
      ? featureTemplate.content.firstElementChild.cloneNode(true)
      : (function(){
          const article = document.createElement('article');
          article.className = 'a11y-card';
          article.innerHTML = '<div class="meta"><span class="label"></span><span class="hint" hidden></span></div>' +
            '<label class="a11y-switch"><input type="checkbox" data-feature="" />' +
            '<span class="track"></span><span class="thumb"></span></label>';
          return article;
        })();

    const labelEl = card.querySelector('.label');
    if(labelEl){ labelEl.textContent = feature.label || ''; }

    const hintEl = card.querySelector('.hint');
    if(hintEl){
      if(feature.hint){
        hintEl.textContent = feature.hint;
        hintEl.hidden = false;
      } else {
        hintEl.textContent = '';
        hintEl.hidden = true;
      }
    }

    const input = card.querySelector('input[type="checkbox"]');
    if(input){
      input.dataset.feature = feature.slug;
      const aria = feature.aria_label || feature.label || feature.slug;
      if(aria){ input.setAttribute('aria-label', aria); }
      ensureFeatureState(feature.slug);
      input.checked = featureState.get(feature.slug) === true;
      input.addEventListener('change', () => {
        toggleFeature(feature.slug, input.checked);
      });
      featureInputs.push(input);
    }

    return card;
  }

  function showEmptyMessage(section){
    if(!emptyMessage) return;
    const fallback = emptyMessage.dataset ? (emptyMessage.dataset.defaultEmpty || emptyMessage.textContent) : emptyMessage.textContent;
    let label = fallback;
    if(section && typeof section.empty_label === 'string' && section.empty_label.trim() !== ''){
      label = section.empty_label;
    }
    emptyMessage.textContent = label;
    emptyMessage.hidden = false;
  }

  function hideEmptyMessage(){
    if(emptyMessage){ emptyMessage.hidden = true; }
  }

  function renderSection(index, opts={}){
    if(!panel || !featureGrid || !tabs.length) return;
    if(index < 0 || index >= tabs.length) index = 0;
    activeSectionIndex = index;

    const activeTab = updateTabState(index);
    if(activeTab){ panel.setAttribute('aria-labelledby', activeTab.id); }

    featureInputs = [];
    featureGrid.innerHTML = '';

    const section = Array.isArray(sectionsData) ? sectionsData[index] : null;
    const features = section && Array.isArray(section.features) ? section.features : [];

    if(!features.length){
      showEmptyMessage(section);
      if(opts.focusPanel){ panel.focus(); }
      return;
    }

    hideEmptyMessage();

    features.forEach(feature => {
      if(!feature) return;
      const slug = typeof feature.slug === 'string' ? feature.slug.trim() : '';
      const label = typeof feature.label === 'string' ? feature.label : '';
      if(!slug || !label) return;
      const card = createFeatureCard({
        slug,
        label,
        hint: typeof feature.hint === 'string' ? feature.hint : '',
        aria_label: typeof feature.aria_label === 'string' ? feature.aria_label : ''
      });
      if(card){ featureGrid.appendChild(card); }
    });

    if(opts.focusPanel){ panel.focus(); }
  }

  // ---------- Elements ----------
  const btn = document.getElementById('a11y-launcher');
  const overlay = document.getElementById('a11y-overlay');
  const closeBtn = document.getElementById('a11y-close');
  const closeBtn2 = document.getElementById('a11y-close2');
  const resetBtn = document.getElementById('a11y-reset');

  const nav = document.getElementById('a11y-sections');
  const panel = document.getElementById('a11y-section-panel');
  const featureGrid = panel ? panel.querySelector('[data-role="feature-grid"]') : null;
  const emptyMessage = panel ? panel.querySelector('[data-role="empty"]') : null;
  const featureTemplate = document.getElementById('a11y-feature-template');
  const dataScript = document.getElementById('a11y-widget-data');

  let sectionsData = [];
  if(dataScript){
    try {
      const rawData = dataScript.textContent || dataScript.innerText || '[]';
      const parsed = JSON.parse(rawData);
      if(Array.isArray(parsed)) sectionsData = parsed;
    } catch(err){ sectionsData = []; }
  }

  const tabs = nav ? Array.from(nav.querySelectorAll('[role="tab"]')) : [];
  let featureInputs = [];
  const featureState = new Map();
  let activeSectionIndex = tabs.findIndex(tab => tab.getAttribute('aria-selected') === 'true');
  if(activeSectionIndex < 0) activeSectionIndex = 0;

  let launcherLastPos = null;
  let hasCustomLauncherPosition = false;
  let skipNextClick = false;
  let dragMoved = false;
  let dragging = false;
  let dragPointerId = null;
  let dragOffsetX = 0;
  let dragOffsetY = 0;
  let dragStartPos = null;
  let activeTouchId = null;
  const supportsPointer = 'PointerEvent' in window;

  function getCurrentLauncherPosition(){
    if(!btn){ return { x: 0, y: 0 }; }
    const rect = btn.getBoundingClientRect();
    return { x: rect.left, y: rect.top };
  }

  function clampLauncherPosition(x, y){
    if(!btn){ return { x, y }; }
    const rect = btn.getBoundingClientRect();
    const width = rect.width;
    const height = rect.height;
    const maxX = Math.max(0, window.innerWidth - width);
    const maxY = Math.max(0, window.innerHeight - height);
    return {
      x: Math.min(Math.max(x, 0), maxX),
      y: Math.min(Math.max(y, 0), maxY)
    };
  }

  function applyLauncherPosition(x, y){
    document.documentElement.style.setProperty('--a11y-launcher-x', `${x}px`);
    document.documentElement.style.setProperty('--a11y-launcher-y', `${y}px`);
    launcherLastPos = { x, y };
  }

  function persistLauncherPosition(x, y){
    hasCustomLauncherPosition = true;
    try {
      localStorage.setItem(LAUNCHER_POS_KEY, JSON.stringify({ x, y }));
    } catch(err){ /* ignore */ }
  }

  function restoreLauncherPosition(){
    if(!btn) return;
    try {
      const raw = localStorage.getItem(LAUNCHER_POS_KEY);
      if(!raw) return;
      const data = JSON.parse(raw);
      if(typeof data.x !== 'number' || typeof data.y !== 'number') return;
      const clamped = clampLauncherPosition(data.x, data.y);
      applyLauncherPosition(clamped.x, clamped.y);
      hasCustomLauncherPosition = true;
      if(clamped.x !== data.x || clamped.y !== data.y){
        persistLauncherPosition(clamped.x, clamped.y);
      }
    } catch(err){ /* ignore */ }
  }

  function startDragging(clientX, clientY){
    if(!btn) return;
    skipNextClick = false;
    dragMoved = false;
    const rect = btn.getBoundingClientRect();
    dragOffsetX = clientX - rect.left;
    dragOffsetY = clientY - rect.top;
    dragStartPos = { x: rect.left, y: rect.top };
    launcherLastPos = { x: rect.left, y: rect.top };
    dragging = true;
  }

  function moveDragging(clientX, clientY){
    if(!dragging) return;
    const targetX = clientX - dragOffsetX;
    const targetY = clientY - dragOffsetY;
    const clamped = clampLauncherPosition(targetX, targetY);
    applyLauncherPosition(clamped.x, clamped.y);
    if(Math.abs(clamped.x - dragStartPos.x) > 1 || Math.abs(clamped.y - dragStartPos.y) > 1){
      dragMoved = true;
    }
  }

  function endDragging(){
    if(!dragging) return;
    dragging = false;
    dragPointerId = null;
    activeTouchId = null;
    if(dragMoved && launcherLastPos){
      persistLauncherPosition(launcherLastPos.x, launcherLastPos.y);
      skipNextClick = true;
      setTimeout(()=>{ skipNextClick = false; }, 0);
    } else {
      skipNextClick = false;
    }
    dragMoved = false;
  }

  function handleResize(){
    if(!btn || !hasCustomLauncherPosition) return;
    const current = getCurrentLauncherPosition();
    const clamped = clampLauncherPosition(current.x, current.y);
    if(clamped.x !== current.x || clamped.y !== current.y){
      applyLauncherPosition(clamped.x, clamped.y);
      persistLauncherPosition(clamped.x, clamped.y);
    }
  }

  function onPointerDown(e){
    if(e.pointerType === 'mouse' && e.button !== 0) return;
    dragPointerId = e.pointerId;
    startDragging(e.clientX, e.clientY);
    if(btn.setPointerCapture){
      try { btn.setPointerCapture(dragPointerId); } catch(err){}
    }
    if(e.pointerType !== 'mouse'){
      e.preventDefault();
    }
  }

  function onPointerMove(e){
    if(!dragging || e.pointerId !== dragPointerId) return;
    moveDragging(e.clientX, e.clientY);
  }

  function onPointerUp(e){
    if(e.pointerId !== dragPointerId) return;
    if(btn.releasePointerCapture){
      try { btn.releasePointerCapture(dragPointerId); } catch(err){}
    }
    endDragging();
  }

  function findTouchById(touchList, id){
    for(let i=0;i<touchList.length;i++){
      if(touchList[i].identifier === id) return touchList[i];
    }
    return null;
  }

  function onTouchStart(e){
    if(e.touches.length > 1) return;
    const touch = e.changedTouches[0];
    if(!touch) return;
    activeTouchId = touch.identifier;
    startDragging(touch.clientX, touch.clientY);
    e.preventDefault();
  }

  function onTouchMove(e){
    if(activeTouchId === null) return;
    const touch = findTouchById(e.changedTouches, activeTouchId);
    if(!touch) return;
    moveDragging(touch.clientX, touch.clientY);
    e.preventDefault();
  }

  function onTouchEnd(e){
    if(activeTouchId === null) return;
    const touch = findTouchById(e.changedTouches, activeTouchId);
    if(!touch) return;
    endDragging();
  }

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
    featureState.forEach((value, key) => {
      data[key] = value;
    });
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch(err){ /* ignore */ }
  }
  function restore(){
    try {
      const raw = localStorage.getItem(STORAGE_KEY); if(!raw) return;
      const data = JSON.parse(raw);
      for(const key in data){
        const val = !!data[key];
        toggleFeature(key, val, {silent:true});
      }
    } catch(err){ /* ignore */ }
  }

  // ---------- Core toggle ----------
  function toggleFeature(key, on, opts={}){
    if(!key) return;
    const attr = 'a11y' + dashToCamel(key);
    const normalized = !!on;
    ensureFeatureState(key);
    featureState.set(key, normalized);
    if(normalized) document.documentElement.dataset[attr] = 'on';
    else delete document.documentElement.dataset[attr];

    const ev = new CustomEvent('a11y:toggle', { detail: { key, on } });
    window.dispatchEvent(ev);

    const set = listeners.get(key);
    if(set) for(const fn of set) try { fn(on, key); } catch(e){}

    if(!opts.skipInputSync){
      const input = featureInputs.find(i=>i.dataset.feature===key);
      if(input && input.checked !== normalized){ input.checked = normalized; }
    }

    if(!opts.silent) persist();
  }

  // ---------- Wiring ----------
  if(btn){
    btn.addEventListener('click', (e)=>{
      if(skipNextClick){
        e.preventDefault();
        e.stopImmediatePropagation();
        skipNextClick = false;
        return;
      }
      openPanel();
    });

    if(supportsPointer){
      btn.addEventListener('pointerdown', onPointerDown);
      btn.addEventListener('pointermove', onPointerMove);
      btn.addEventListener('pointerup', onPointerUp);
      btn.addEventListener('pointercancel', onPointerUp);
    } else {
      btn.addEventListener('touchstart', onTouchStart, { passive: false });
      window.addEventListener('touchmove', onTouchMove, { passive: false });
      window.addEventListener('touchend', onTouchEnd);
      window.addEventListener('touchcancel', onTouchEnd);
    }
  }
  if(overlay){
    overlay.addEventListener('click', (e)=>{ if(e.target === overlay) closePanel(); });
  }
  if(closeBtn){ closeBtn.addEventListener('click', closePanel); }
  if(closeBtn2){ closeBtn2.addEventListener('click', closePanel); }
  if(resetBtn){
    resetBtn.addEventListener('click', ()=>{
      featureState.forEach((_, key)=>{ toggleFeature(key, false, {silent:true}); });
      try { localStorage.removeItem(STORAGE_KEY); } catch(err){}
      try { localStorage.removeItem(LAUNCHER_POS_KEY); } catch(err){}
      document.documentElement.style.removeProperty('--a11y-launcher-x');
      document.documentElement.style.removeProperty('--a11y-launcher-y');
      launcherLastPos = null;
      hasCustomLauncherPosition = false;
    });
  }
  if(nav && tabs.length){
    nav.addEventListener('click', (e)=>{
      const tab = e.target.closest('[role="tab"]');
      if(!tab || !nav.contains(tab)) return;
      const index = tabs.indexOf(tab);
      if(index === -1) return;
      e.preventDefault();
      if(index !== activeSectionIndex){
        renderSection(index);
      }
    });

    nav.addEventListener('keydown', (e)=>{
      const key = e.key;
      const currentIndex = tabs.indexOf(document.activeElement);
      if(currentIndex === -1) return;

      if(key === 'Enter' || key === ' ' || key === 'Spacebar'){
        e.preventDefault();
        renderSection(currentIndex);
        return;
      }

      let targetIndex = null;
      if(key === 'ArrowRight' || key === 'ArrowDown'){
        targetIndex = (currentIndex + 1) % tabs.length;
      } else if(key === 'ArrowLeft' || key === 'ArrowUp'){
        targetIndex = (currentIndex - 1 + tabs.length) % tabs.length;
      } else if(key === 'Home'){
        targetIndex = 0;
      } else if(key === 'End'){
        targetIndex = tabs.length - 1;
      }

      if(targetIndex === null) return;
      e.preventDefault();
      const nextTab = tabs[targetIndex];
      if(nextTab){
        nextTab.focus();
        renderSection(targetIndex);
      }
    });
  }

  seedFeatureState();
  restore();

  if(nav && tabs.length && panel && featureGrid){
    renderSection(activeSectionIndex);
  } else {
    featureInputs = Array.from(document.querySelectorAll('[data-feature]'));
    featureInputs.forEach(input => {
      if(!input || !input.dataset) return;
      ensureFeatureState(input.dataset.feature);
      input.checked = featureState.get(input.dataset.feature) === true;
      input.addEventListener('change', () => toggleFeature(input.dataset.feature, input.checked));
    });
  }

  restoreLauncherPosition();
  if(btn){ window.addEventListener('resize', handleResize); }

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
