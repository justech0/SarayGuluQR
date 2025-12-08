        </main>
    </div>
</div>
<script>
  function safeStorageGet(key, fallback = null) {
    try {
      if (!window.localStorage) return fallback;
      return localStorage.getItem(key) ?? fallback;
    } catch (e) {
      return fallback;
    }
  }

  function safeStorageSet(key, value) {
    try {
      if (!window.localStorage) return;
      localStorage.setItem(key, value);
    } catch (e) {
      // storage disabled; ignore
    }
  }

  const mobileMenuBtn = document.getElementById('mobileMenuButton');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileMenuClose = document.getElementById('mobileMenuClose');

  const toggleMenu = (open) => {
    if (!mobileMenu) return;
    if (open) {
        mobileMenu.classList.remove('hidden');
    } else {
        mobileMenu.classList.add('hidden');
    }
  };

  mobileMenuBtn?.addEventListener('click', () => toggleMenu(true));
  mobileMenuClose?.addEventListener('click', () => toggleMenu(false));
  mobileMenu?.addEventListener('click', (e) => {
    if (e.target === mobileMenu) toggleMenu(false);
  });
</script>
</body>
</html>
