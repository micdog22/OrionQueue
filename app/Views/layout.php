<?php /** @var array $config */ ?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title><?= e($config['app_name'] ?? 'OrionQueue') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
  <header class="topbar">
    <div class="wrap">
      <strong><?= e($config['app_name']) ?></strong>
      <nav>
        <?php if (!empty($_SESSION['is_auth'])): ?>
          <a href="/">Jobs</a>
          <a href="/?new=1">Novo Job</a>
          <a class="danger" href="/logout">Sair</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="wrap">
    <?php if (!empty($flash)): ?>
      <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>
    <?= $content ?>
  </main>
  <footer class="footer wrap">
    <small>© <?= date('Y') ?> OrionQueue</small>
  </footer>
</body>
</html>
