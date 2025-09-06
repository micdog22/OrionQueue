<?php ob_start(); ?>
<h1>Jobs</h1>

<form class="filters" method="get" action="/">
  <label>Status</label>
  <input type="text" name="status" value="<?= e($filters['status'] ?? '') ?>" placeholder="queued, running, done, failed, canceled">
  <label>Fila</label>
  <input type="text" name="queue" value="<?= e($filters['queue'] ?? '') ?>" placeholder="default">
  <button>Filtrar</button>
  <a class="btn" href="/?new=1">Novo Job</a>
</form>

<table class="table">
  <thead>
    <tr>
      <th>ID</th><th>Tipo</th><th>Fila</th><th>Status</th><th>Tent.</th><th>Max</th><th>Disponível</th><th>Prioridade</th><th>Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($jobs as $j): ?>
    <tr>
      <td><?= (int)$j['id'] ?></td>
      <td><?= e($j['type']) ?></td>
      <td><?= e($j['queue']) ?></td>
      <td><?= e($j['status']) ?></td>
      <td><?= (int)$j['attempts'] ?></td>
      <td><?= (int)$j['max_attempts'] ?></td>
      <td><?= date('Y-m-d H:i:s', (int)$j['available_at']) ?></td>
      <td><?= (int)$j['priority'] ?></td>
      <td>
        <form class="inline" method="post" action="/cancel">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">
          <button class="danger" onclick="return confirm('Cancelar job?');">Cancelar</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php $content = ob_get_clean();
view('layout', compact('content','config','flash'));
