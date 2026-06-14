// A project's media[] holds image URLs or YouTube/Vimeo links;
// resolveMedia() works out which to render.
function resolveMedia(url) {
  let m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/);
  if (m) return {
    type: 'video',
    embed: `https://www.youtube.com/embed/${m[1]}`,
    poster: `https://img.youtube.com/vi/${m[1]}/hqdefault.jpg`
  };
  m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
  if (m) return {
    type: 'video',
    embed: `https://player.vimeo.com/video/${m[1]}`,
    poster: null // Vimeo has no fixed thumbnail; falls back to a dark tile
  };
  return { type: 'image', src: url };
}

// Project data. media holds image URLs and/or YouTube/Vimeo links in
// display order; cover is the still image shown on the carousel card.
const img = (seed, n) => Array.from({ length: n }, (_, i) => `https://picsum.photos/seed/${seed}${i}/1200/800`);
// Sample video links; replace with the real YouTube/Vimeo URLs.
const YT_SAMPLE = 'https://www.youtube.com/watch?v=aqz-KE-bpKQ';
const VM_SAMPLE = 'https://vimeo.com/76979871';

const FALLBACK_PROJECTS = [
  { id:'cn1', category:'Construction', type:'Photo + Video', title:'Skyline Tower — Site Progress', location:'Calgary, AB', date:'Ongoing 2024', services:['Site Progress & Drone Media','Cinematic Video Production'], cover:'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=70', media:[YT_SAMPLE, ...img('cn1', 7)] },
  { id:'cn2', category:'Construction', type:'Photo', title:'Harbourfront Development', location:'Kowloon Bay, Hong Kong', date:'2023–2024', services:['Site Progress & Drone Media'], cover:'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=70', media: img('cn2', 7) },
  { id:'ho1', category:'Hospitality', type:'Photo + Video', title:'The Peak Bistro Launch', location:'Central, Hong Kong', date:'April 2024', services:['Cinematic Video Production','Commercial Photography','Social Media Management'], cover:'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=70', media:[VM_SAMPLE, ...img('ho1', 8)] },
  { id:'ho2', category:'Hospitality', type:'Photo + Video', title:'Lakeview Resort Promo', location:'Banff, AB', date:'January 2024', services:['Cinematic Video Production','Commercial Photography'], cover:'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=70', media:[YT_SAMPLE, ...img('ho2', 6)] },
  { id:'cb1', category:'Commercial & Brand', type:'Photo + Video', title:'Nexus Tech Product Launch', location:'Causeway Bay, Hong Kong', date:'May 2024', services:['Short-Form Video Marketing','Commercial Photography'], cover:'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800&q=70', media:[YT_SAMPLE, ...img('cb1', 6)] },
  { id:'cb2', category:'Commercial & Brand', type:'Photo', title:'Aurora Apparel Campaign', location:'Calgary, AB', date:'February 2024', services:['Commercial Photography','Short-Form Video Marketing','Social Media Management'], cover:'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=70', media: img('cb2', 7) },
  { id:'cb3', category:'Commercial & Brand', type:'Photo', title:'Meridian Brand Refresh', location:'Hong Kong', date:'April 2024', services:['Web Design & Google SEO','Commercial Photography'], cover:'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=800&q=70', media: img('cb3', 6) },
  { id:'ce1', category:'Corporate Events', type:'Photo + Video', title:'FinSummit Annual Gala 2024', location:'Wan Chai, Hong Kong', date:'February 2024', services:['Cinematic Video Production','Commercial Photography'], cover:'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&q=70', media:[YT_SAMPLE, ...img('ce1', 6)] },
  { id:'ce2', category:'Corporate Events', type:'Video', title:'TechCon Keynote', location:'Calgary, AB', date:'November 2023', services:['Cinematic Video Production','Social Media Management'], cover:'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=800&q=70', media:[VM_SAMPLE, YT_SAMPLE] },
  { id:'ce3', category:'Corporate Events', type:'Photo + Video', title:'City Music Festival', location:'West Kowloon, Hong Kong', date:'November 2023', services:['Cinematic Video Production','Short-Form Video Marketing'], cover:'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800&q=70', media:[YT_SAMPLE, ...img('ce3', 6)] },
];

// Live data comes from the API; falls back to the seed list above.
let PROJECTS = FALLBACK_PROJECTS;
async function loadProjects() {
  try {
    const res = await fetch('/api/projects.php');
    if (!res.ok) throw new Error('bad status');
    const data = await res.json();
    if (Array.isArray(data) && data.length) PROJECTS = data;
  } catch (e) {
    console.warn('Using fallback projects (API unavailable):', e.message);
  }
}

// Navigation
const nav = document.getElementById('nav');
const navToggle = document.getElementById('navToggle');
const mobileMenu = document.getElementById('mobileMenu');
const mobileClose = document.getElementById('mobileClose');

window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 50));

function openMenu() {
  mobileMenu.classList.add('open');
  navToggle.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeMenu() {
  mobileMenu.classList.remove('open');
  navToggle.classList.remove('open');
  document.body.style.overflow = '';
}
navToggle.addEventListener('click', () => mobileMenu.classList.contains('open') ? closeMenu() : openMenu());
mobileClose.addEventListener('click', closeMenu);
document.querySelectorAll('.mobile-link').forEach(l => l.addEventListener('click', closeMenu));

// Logo / #home links: scroll to top and strip the hash
document.querySelectorAll('a[href="#home"]').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    history.replaceState(null, '', location.pathname + location.search);
  });
});

// Scroll reveal + stat counters
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(en => {
    if (en.isIntersecting) { en.target.classList.add('visible'); revealObs.unobserve(en.target); }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

const counterObs = new IntersectionObserver(entries => {
  entries.forEach(en => {
    if (!en.isIntersecting) return;
    const el = en.target;
    const target = parseInt(el.dataset.target, 10);
    const dur = 1500;
    const start = performance.now();
    (function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.floor(eased * target);
      if (p < 1) requestAnimationFrame(tick);
    })(start);
    counterObs.unobserve(el);
  });
}, { threshold: 0.5 });
document.querySelectorAll('.stat-num[data-target]').forEach(el => counterObs.observe(el));

// Portfolio carousel
const projectsGrid = document.getElementById('projectsGrid');
let filteredProjects = [];
let currentSlide = 0;

const slideHTML = (p, i) => `
  <div class="carousel-slide">
    <div class="carousel-card">
      <div class="carousel-img-side">
        <img src="${p.cover}" alt="${p.title}" loading="${i === 0 ? 'eager' : 'lazy'}">
        <span class="carousel-type-pill${/video/i.test(p.type) ? ' has-video' : ''}">${p.type}</span>
      </div>
      <div class="carousel-info-side">
        <span class="carousel-cat">${p.category}</span>
        <h3 class="carousel-title">${p.title}</h3>
        <div class="carousel-meta"><span>${p.location}</span><span>${p.date}</span></div>
        <p class="carousel-services">${p.services.join(' · ')}</p>
        <button class="btn btn-primary carousel-cta" data-id="${p.id}">View Project <span class="photo-count">${p.media.length}</span></button>
      </div>
    </div>
  </div>`;

function renderCarousel(filter) {
  filteredProjects = filter === 'all' ? PROJECTS : PROJECTS.filter(p => p.category === filter);
  currentSlide = 0;

  if (!filteredProjects.length) {
    projectsGrid.innerHTML = '<div class="carousel-empty">No projects in this category yet.</div>';
    return;
  }

  projectsGrid.innerHTML = `
    <div class="carousel-wrapper">
      <div class="carousel-viewport">
        <div class="carousel-track" id="carouselTrack">
          ${filteredProjects.map(slideHTML).join('')}
        </div>
      </div>
      <button class="carousel-btn carousel-prev" id="carouselPrev" aria-label="Previous">‹</button>
      <button class="carousel-btn carousel-next" id="carouselNext" aria-label="Next">›</button>
    </div>
    <div class="carousel-controls">
      <div class="carousel-dots">
        ${filteredProjects.map((p, i) => `<button class="carousel-dot${i === 0 ? ' active' : ''}" data-slide="${i}" aria-label="Slide ${i + 1}"></button>`).join('')}
      </div>
      <div class="carousel-counter" id="carouselCounter">1 / ${filteredProjects.length}</div>
    </div>`;

  document.getElementById('carouselPrev').addEventListener('click', () => shiftSlide(-1));
  document.getElementById('carouselNext').addEventListener('click', () => shiftSlide(1));
  document.querySelectorAll('.carousel-dot').forEach(dot =>
    dot.addEventListener('click', () => goToSlide(parseInt(dot.dataset.slide, 10))));
  document.querySelectorAll('.carousel-cta').forEach(btn =>
    btn.addEventListener('click', () => openProjectModal(btn.dataset.id)));
  setupSwipe();
}

function goToSlide(index) {
  currentSlide = index;
  const track = document.getElementById('carouselTrack');
  if (track) track.style.transform = `translateX(-${index * 100}%)`;
  document.querySelectorAll('.carousel-dot').forEach((d, i) => d.classList.toggle('active', i === index));
  const counter = document.getElementById('carouselCounter');
  if (counter) counter.textContent = `${index + 1} / ${filteredProjects.length}`;
}

function shiftSlide(dir) {
  goToSlide((currentSlide + dir + filteredProjects.length) % filteredProjects.length);
}

function setupSwipe() {
  const track = projectsGrid.querySelector('.carousel-track');
  if (!track) return;
  let startX = 0;
  track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
  track.addEventListener('touchend', e => {
    const delta = startX - e.changedTouches[0].clientX;
    if (Math.abs(delta) > 50) shiftSlide(delta > 0 ? 1 : -1);
  }, { passive: true });
}

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderCarousel(btn.dataset.filter);
  });
});

loadProjects().then(() => renderCarousel('all'));

function isVideoUrl(u) {
  return /\.(mp4|webm|ogg|ogv|m4v|mov)(\?|#|$)/i.test(u || '');
}

// The hero slot can hold a still image or a background video. When it's a
// video, replace the <img> with a muted, looping <video> that still covers
// the area (it inherits the .hero-img styling).
function swapHeroVideo(el, url) {
  let v = el;
  if (el.tagName !== 'VIDEO') {
    v = document.createElement('video');
    v.className = el.className;
    v.setAttribute('data-img-key', 'hero');
    v.autoplay = true; v.loop = true; v.muted = true; v.playsInline = true;
    v.setAttribute('autoplay', ''); v.setAttribute('loop', '');
    v.setAttribute('muted', ''); v.setAttribute('playsinline', '');
    el.replaceWith(v);
  }
  if (v.src !== url) v.src = url;
  const p = v.play(); if (p) p.catch(() => {});
}

// Apply admin image overrides to [data-img-key] elements.
async function applySiteImages() {
  try {
    const res = await fetch('/api/site_images.php');
    if (!res.ok) return;
    const map = await res.json();
    document.querySelectorAll('[data-img-key]').forEach(el => {
      const k = el.getAttribute('data-img-key');
      const url = map[k];
      if (!url) return;
      if (k === 'hero' && isVideoUrl(url)) { swapHeroVideo(el, url); return; }
      el.src = url;
    });
  } catch (e) {
    /* keep the HTML defaults on error */
  }
}
applySiteImages();

// Apply admin text overrides to [data-text-key] elements.
async function applySiteText() {
  try {
    const res = await fetch('/api/site_text.php');
    if (!res.ok) return;
    const map = await res.json();
    document.querySelectorAll('[data-text-key]').forEach(el => {
      const k = el.getAttribute('data-text-key');
      if (map[k] != null && map[k] !== '') el.textContent = map[k];
    });
  } catch (e) {
    /* keep the HTML defaults on error */
  }
}
applySiteText();

// Project detail modal
const projectModal = document.getElementById('projectModal');
const modalBreadcrumb = document.getElementById('modalBreadcrumb');
const projectModalInfo = document.getElementById('projectModalInfo');
const projectPhotoGrid = document.getElementById('projectPhotoGrid');
let currentProjectMedia = [];

function openProjectModal(id) {
  const p = PROJECTS.find(x => x.id === id);
  if (!p) return;
  currentProjectMedia = p.media;

  modalBreadcrumb.innerHTML = `${p.category} &nbsp;·&nbsp; <strong>${p.title}</strong>`;
  projectModalInfo.innerHTML = `
    <h2 class="modal-project-title">${p.title}</h2>
    <div class="modal-project-meta">
      <span>${p.category}</span><span>${p.location}</span><span>${p.date}</span><span>${p.media.length} items</span>
    </div>`;

  projectPhotoGrid.innerHTML = p.media.map((url, i) => {
    const m = resolveMedia(url);
    if (m.type === 'video') {
      const poster = m.poster
        ? `<img src="${m.poster}" alt="" loading="lazy">`
        : `<div class="video-poster-fallback"></div>`;
      return `<div class="project-photo-item is-video" data-index="${i}">${poster}<div class="photo-hover">▶</div></div>`;
    }
    return `<div class="project-photo-item" data-index="${i}"><img src="${m.src}" alt="" loading="lazy"><div class="photo-hover">+</div></div>`;
  }).join('');

  projectPhotoGrid.querySelectorAll('.project-photo-item').forEach(item =>
    item.addEventListener('click', () => openLightbox(parseInt(item.dataset.index, 10))));

  projectModal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeProjectModal() {
  projectModal.classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('modalBack').addEventListener('click', closeProjectModal);
document.getElementById('modalClose').addEventListener('click', closeProjectModal);

// Lightbox (image or video)
const lightbox = document.getElementById('lightbox');
const lightboxContent = document.getElementById('lightboxContent');
let currentLightboxIdx = 0;

function renderLightbox(idx) {
  currentLightboxIdx = idx;
  const m = resolveMedia(currentProjectMedia[idx]);
  lightboxContent.innerHTML = m.type === 'video'
    ? `<div class="lightbox-video"><iframe src="${m.embed}?autoplay=1&rel=0" title="Project video" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen></iframe></div>`
    : `<img src="${m.src}" alt="">`;
}
function openLightbox(idx) { renderLightbox(idx); lightbox.classList.add('open'); }
function closeLightbox() { lightbox.classList.remove('open'); lightboxContent.innerHTML = ''; } // clearing stops video playback
function nextLightbox() { renderLightbox((currentLightboxIdx + 1) % currentProjectMedia.length); }
function prevLightbox() { renderLightbox((currentLightboxIdx - 1 + currentProjectMedia.length) % currentProjectMedia.length); }

document.getElementById('lightboxClose').addEventListener('click', closeLightbox);
document.getElementById('lightboxNext').addEventListener('click', nextLightbox);
document.getElementById('lightboxPrev').addEventListener('click', prevLightbox);
lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });

// Keyboard shortcuts (lightbox > modal > carousel)
document.addEventListener('keydown', e => {
  if (lightbox.classList.contains('open')) {
    if (e.key === 'Escape') closeLightbox();
    else if (e.key === 'ArrowRight') nextLightbox();
    else if (e.key === 'ArrowLeft') prevLightbox();
    return;
  }
  if (projectModal.classList.contains('open')) {
    if (e.key === 'Escape') closeProjectModal();
    return;
  }
  if (filteredProjects.length) {
    if (e.key === 'ArrowRight') shiftSlide(1);
    else if (e.key === 'ArrowLeft') shiftSlide(-1);
  }
});

// Booking form (multi-step)
function goToStep(step) {
  document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
  const target = document.getElementById(step === 'success' ? 'stepSuccess' : 'step' + step);
  if (target) target.classList.add('active');

  document.querySelectorAll('.step').forEach(el => {
    const n = parseInt(el.dataset.step, 10);
    el.classList.remove('active', 'completed');
    if (typeof step === 'number') {
      if (n < step) el.classList.add('completed');
      if (n === step) el.classList.add('active');
    } else {
      el.classList.add('completed'); // mark all steps done
    }
  });

  document.getElementById('booking').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.querySelectorAll('.next-step').forEach(btn => {
  btn.addEventListener('click', () => {
    const next = parseInt(btn.dataset.next, 10);
    if (next === 2) {
      const err = document.getElementById('step1Error');
      if (!document.querySelector('input[name="eventType"]:checked')) {
        err.textContent = 'Please select an industry sector to continue.';
        return;
      }
      err.textContent = '';
    }
    goToStep(next);
  });
});

document.querySelectorAll('.prev-step').forEach(btn =>
  btn.addEventListener('click', () => goToStep(parseInt(btn.dataset.prev, 10))));

document.getElementById('bookingForm').addEventListener('submit', async e => {
  e.preventDefault();
  const f = e.target;
  const name = f.querySelector('[name="name"]').value.trim();
  const email = f.querySelector('[name="email"]').value.trim();
  if (!name || !email) return;
  const data = {
    name, email,
    phone: f.querySelector('[name="phone"]').value.trim(),
    eventType: (f.querySelector('input[name="eventType"]:checked') || {}).value || '',
    date: f.querySelector('[name="date"]').value,
    location: f.querySelector('[name="location"]').value.trim(),
    services: [...f.querySelectorAll('input[name="service"]:checked')].map(c => c.value),
    notes: f.querySelector('[name="notes"]').value.trim(),
    newsletter: f.querySelector('[name="newsletter"]').checked,
  };
  try {
    await fetch('/api/bookings.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  } catch (err) { /* keep the success state even if the request fails */ }
  goToStep('success');
});

// Newsletter / brochure signup
document.getElementById('signupForm').addEventListener('submit', async e => {
  e.preventDefault();
  const email = e.target.querySelector('[name="subEmail"]').value.trim();
  if (!email) return;
  const name = (e.target.querySelector('[name="subName"]') || {}).value || '';
  try {
    await fetch('/api/subscribers.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name, email }) });
  } catch (err) { /* keep the success state even if the request fails */ }
  e.target.style.display = 'none';
  document.getElementById('signupSuccess').style.display = 'block';
});
