/**
 * welcome.js — v13.0
 */
document.addEventListener('DOMContentLoaded', () => {

  /* 1. ESTADO PASO */
  const currentStep = parseInt(
    document.body.dataset.currentStep
    || document.querySelector('[data-current-step]')?.dataset.currentStep
    || '0'
  );

  /* 2. MARCAR PASOS */
  const steps = document.querySelectorAll('.sb-step');
  steps.forEach((step, i) => {
    const n   = i + 1;
    const num = step.querySelector('.sb-step-num');
    if (n < currentStep) {
      step.classList.add('done');
      if (num) num.innerHTML = '<i class="fas fa-check" style="font-size:11px"></i>';
    } else if (n === currentStep) {
      step.classList.add('active-step');
    }
  });

  /* 3. PROGRESO */
  const progressFill = document.querySelector('.progress-fill');
  const progressText = document.querySelector('.progress-text');
  const total = steps.length || 6;
  const done  = Math.max(0, currentStep - 1);
  setTimeout(() => {
    if (progressFill) progressFill.style.width = ((done / total) * 100) + '%';
    if (progressText) progressText.textContent  = `${done}/${total} completado`;
  }, 300);

  /* 4. HOVER DIMMING SIDEBAR */
  steps.forEach(s => {
    s.addEventListener('mouseenter', () => steps.forEach(x => { if (x !== s) x.style.opacity = '.28'; }));
    s.addEventListener('mouseleave', () => steps.forEach(x => { x.style.opacity = ''; }));
  });

  /* 5. HOVER DIMMING FEATURES BAR */
  const feats = document.querySelectorAll('.features-bar .feature-item');
  feats.forEach(f => {
    f.addEventListener('mouseenter', () => feats.forEach(x => { if (x !== f) x.style.opacity = '.28'; }));
    f.addEventListener('mouseleave', () => feats.forEach(x => { x.style.opacity = ''; }));
  });

  /* 6. CONTADOR ANIMADO */
  function countUp(el, target, dur = 900) {
    if (isNaN(target)) return;
    const hasPct = el.dataset.pct !== undefined;
    let start = null;
    const run = ts => {
      if (!start) start = ts;
      const p = Math.min((ts - start) / dur, 1);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))) + (hasPct ? '%' : '');
      if (p < 1) requestAnimationFrame(run);
    };
    requestAnimationFrame(run);
  }
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target;
      const n  = parseInt(el.dataset.count ?? el.dataset.pct ?? el.textContent.replace('%',''));
      if (!isNaN(n)) countUp(el, n);
      obs.unobserve(el);
    });
  }, { threshold: 0.4 });
  document.querySelectorAll('.stat-value, .stat-number').forEach(el => obs.observe(el));

  /* 7. CONTADORES REALES */
  async function loadCount(url, elId) {
    const el = document.getElementById(elId);
    if (!el) return;
    try {
      const resp = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      if (!resp.ok) throw new Error();
      const data = await resp.json();
      let val = null;
      if (Array.isArray(data))            val = data.length;
      else if (Array.isArray(data.data))  val = data.data.length;
      else if (data.total !== undefined)  val = data.total;
      else if (data.count !== undefined)  val = data.count;
      el.textContent = val !== null ? val : '—';
    } catch { el.textContent = '—'; }
  }
  loadCount('/asignaturas', 'count-materias');
  loadCount('/profesores',  'count-docentes');
  loadCount('/grados',      'count-grupos');

  /* 8. PARALLAX SUAVE en la card */
  const card  = document.querySelector('.card-preview');
  const heroR = document.querySelector('.hero-right');
  let tX=0,tY=0,cX=0,cY=0,rafId=null;
  const lerp = (a,b,t) => a+(b-a)*t;
  function tick(){
    cX=lerp(cX,tX,.06); cY=lerp(cY,tY,.06);
    if(card) card.style.transform=`translateY(${cY*-5}px) translateX(${cX*3}px)`;
    rafId=(Math.abs(tX-cX)+Math.abs(tY-cY))>.005?requestAnimationFrame(tick):null;
  }
  if(heroR){
    heroR.addEventListener('mousemove',e=>{
      const r=heroR.getBoundingClientRect();
      tX=(e.clientX-r.left-r.width/2)/(r.width/2);
      tY=(e.clientY-r.top-r.height/2)/(r.height/2);
      if(!rafId) rafId=requestAnimationFrame(tick);
    });
    heroR.addEventListener('mouseleave',()=>{tX=0;tY=0;if(!rafId) rafId=requestAnimationFrame(tick);});
  }

});