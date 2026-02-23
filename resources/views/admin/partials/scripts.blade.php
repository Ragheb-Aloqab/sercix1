<script>
  const $ = (id) => document.getElementById(id);

  const sidebar = $('sidebar');
  const backdrop = $('backdrop');

  const getSidebarHideClass = () => document.documentElement.getAttribute('dir') === 'rtl' ? 'translate-x-full' : '-translate-x-full';

  const openSidebar = () => {
    sidebar.classList.remove(getSidebarHideClass());
    backdrop.classList.remove('hidden');
  };
  const closeSidebar = () => {
    sidebar.classList.add(getSidebarHideClass());
    backdrop.classList.add('hidden');
  };

  const syncSidebarVisibility = () => {
    if (!sidebar || !backdrop) return;
    const hideClass = getSidebarHideClass();
    if (window.innerWidth >= 1024) {
      sidebar.classList.remove(hideClass);
      backdrop.classList.add('hidden');
    } else {
      sidebar.classList.add(hideClass);
    }
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

  window.addEventListener('resize', syncSidebarVisibility);

  document.addEventListener('DOMContentLoaded', () => {
    if (sidebar && window.innerWidth < 1024) {
      sidebar.classList.add(getSidebarHideClass());
    }
  });
</script>
