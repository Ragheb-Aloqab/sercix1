<script>
  const $ = (id) => document.getElementById(id);

  const sidebar = $('sidebar');
  const backdrop = $('backdrop');

  const openSidebar = () => {
    sidebar.classList.remove('translate-x-full');
    backdrop.classList.remove('hidden');
  };
  const closeSidebar = () => {
    sidebar.classList.add('translate-x-full');
    backdrop.classList.add('hidden');
  };

  if ($('openSidebar')) $('openSidebar').addEventListener('click', openSidebar);
  if ($('closeSidebar')) $('closeSidebar').addEventListener('click', closeSidebar);
  if (backdrop) backdrop.addEventListener('click', closeSidebar);

  if ($('toggleTheme')) {
    $('toggleTheme').addEventListener('click', () => {
      const html = document.documentElement;
      html.classList.toggle('dark');

      const icon = $('toggleTheme').querySelector('i');
      const isDark = html.classList.contains('dark');
      icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });
  }

  if ($('toggleDir')) {
    $('toggleDir').addEventListener('click', () => {
      const html = document.documentElement;
      const isRTL = html.getAttribute('dir') === 'rtl';
      html.setAttribute('dir', isRTL ? 'ltr' : 'rtl');
      html.setAttribute('lang', isRTL ? 'en' : 'ar');
    });
  }

  window.addEventListener('resize', () => {
    if (!sidebar || !backdrop) return;
    if (window.innerWidth >= 1024) {
      backdrop.classList.add('hidden');
      sidebar.classList.remove('translate-x-full');
    } else {
      sidebar.classList.add('translate-x-full');
    }
  });
</script>
