/* app.js — interaksi Glowify */
(function () {
  // Helpers
  const $ = (s, sc=document) => sc.querySelector(s);
  const $$ = (s, sc=document) => Array.from(sc.querySelectorAll(s));

  // Sticky header shadow saat scroll
  const navbar = $('.navbar');
  const onScroll = () => {
    const scrolled = window.scrollY > 6;
    navbar.style.boxShadow = scrolled ? '0 6px 24px rgba(17,24,39,.08)' : 'none';
  };
  window.addEventListener('scroll', onScroll); onScroll();

  // Dark mode toggle
  const root = document.documentElement;
  const KEY = 'glowify-theme';
  const saved = localStorage.getItem(KEY);
  if (saved === 'dark') root.classList.add('dark');
  const btnTheme = $('#theme-toggle');
  if (btnTheme) {
    btnTheme.addEventListener('click', () => {
      root.classList.toggle('dark');
      localStorage.setItem(KEY, root.classList.contains('dark') ? 'dark' : 'light');
    });
  }

  // Toast util
  function toast(msg) {
    let t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {
      position: 'fixed', bottom: '18px', right: '18px', zIndex: 9999,
      background: '#111827', color: '#fff', padding: '10px 14px',
      borderRadius: '12px', boxShadow: '0 8px 30px rgba(0,0,0,.35)',
      opacity: '0', transform: 'translateY(8px)', transition: '.18s ease'
    });
    if (root.classList.contains('dark')) t.style.background = '#374151';
    document.body.appendChild(t);
    requestAnimationFrame(()=>{ t.style.opacity='1'; t.style.transform='translateY(0)'; });
    setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translateY(8px)'; 
      setTimeout(()=>t.remove(), 200);
    }, 1600);
  }

  // Animasi tombol "Tambah ke Keranjang"
  $$('form button[type="submit"]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      btn.classList.add('pop');
      setTimeout(()=>btn.classList.remove('pop'), 250);
    });
  });

  // Tampilkan toast ketika ada elemen .notice (mis. setelah add-to-cart)
  const notice = $('.notice');
  if (notice) toast(notice.textContent.trim());
})();
