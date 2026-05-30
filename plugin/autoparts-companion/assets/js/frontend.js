(function(){
  const api = window.AutoPartsApi || {};
  function qs(form){ return new URLSearchParams(new FormData(form)).toString(); }
  function card(p){
    const img = p.image ? `<img src="${p.image}" alt="" loading="lazy">` : '<div class="ap-noimg">Фото скоро</div>';
    return `<article class="ap-part-card">${img}<div class="ap-part-card__body"><span class="ap-badge">${p.stock||'уточнить'}</span><h3><a href="${p.url}">${p.title}</a></h3><p>${[p.brand,p.model,p.generation].filter(Boolean).join(' · ')}</p><p class="ap-oem">OEM: ${p.oem||'—'}</p><strong class="ap-price">${p.price||'по запросу'} ${p.currency||''}</strong><div class="ap-actions"><a href="${p.url}" class="ap-btn">Подробнее</a><button data-fav="${p.id}">♡</button></div></div></article>`;
  }
  async function loadCatalog(root){
    const form = root.querySelector('[data-ap-filters]');
    const box = root.querySelector('[data-ap-results]');
    box.innerHTML = '<div class="ap-skeleton"></div><div class="ap-skeleton"></div><div class="ap-skeleton"></div>';
    const res = await fetch(`${api.root}/parts?${qs(form)}`);
    const data = await res.json();
    box.innerHTML = data.items && data.items.length ? data.items.map(card).join('') : '<p>Запчасти не найдены. Оставьте заявку на подбор.</p>';
  }
  document.querySelectorAll('[data-ap-catalog]').forEach(root=>{ const form=root.querySelector('[data-ap-filters]'); form.addEventListener('submit', e=>{e.preventDefault();loadCatalog(root)}); root.querySelector('[data-ap-filter-toggle]')?.addEventListener('click',()=>form.classList.toggle('is-open')); loadCatalog(root); });
  document.querySelectorAll('[data-ap-request-form]').forEach(form=>form.addEventListener('submit', async e=>{ e.preventDefault(); const status=form.querySelector('.ap-form-status'); status.textContent='Отправляем...'; const res=await fetch(`${api.root}/requests`,{method:'POST',headers:{'X-WP-Nonce':api.nonce},body:new FormData(form)}); status.textContent=res.ok?'Спасибо! Заявка создана, менеджер скоро свяжется.':'Ошибка отправки. Позвоните нам или попробуйте позже.'; if(res.ok) form.reset(); }));
})();
