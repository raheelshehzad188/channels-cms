<?php defined('BASEPATH') OR exit('No direct script access allowed');
$isAuth = (strpos($this->router->fetch_class(), 'auth') !== false || $this->router->fetch_class() === 'Auth');
?>
<?php if (!$isAuth): ?>
  </main>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  var html = document.documentElement;
  var btn = document.getElementById('themeToggle');
  if (btn) {
    btn.addEventListener('click', function () {
      var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-bs-theme', next);
      btn.innerHTML = next === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
    });
  }
})();
</script>
</body>
</html>
