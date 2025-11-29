        </main>
    </div>
</div>
<script>
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
