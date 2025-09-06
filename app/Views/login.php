<?php ob_start(); ?>
<h1>Acessar painel</h1>
<form method="post" action="/login">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label>E-mail</label>
  <input type="email" name="user" required value="admin@local">
  <label>Senha</label>
  <input type="password" name="pass" required>
  <button type="submit">Entrar</button>
</form>
<?php $content = ob_get_clean();
view('layout', compact('content','config','flash'));
