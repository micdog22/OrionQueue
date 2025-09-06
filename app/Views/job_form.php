<?php ob_start(); ?>
<h1>Novo Job</h1>
<form method="post" action="/create">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

  <label>Tipo</label>
  <select name="type" required>
    <option value="http">http</option>
    <option value="php">php</option>
    <option value="command">command</option>
  </select>

  <label>Fila</label>
  <input type="text" name="queue" value="default" required>

  <label>Payload (JSON)</label>
  <textarea name="payload" rows="6" placeholder='{"url":"https://httpbin.org/get"}'></textarea>

  <label>Disponível em (timestamp UNIX, 0 = agora)</label>
  <input type="number" name="available_at" value="0">

  <label>Prioridade (maior executa primeiro)</label>
  <input type="number" name="priority" value="0">

  <label>Máximo de tentativas</label>
  <input type="number" name="max_attempts" value="5">

  <div class="actions">
    <button type="submit">Enfileirar</button>
    <a class="btn" href="/">Cancelar</a>
  </div>
</form>
<?php $content = ob_get_clean();
view('layout', compact('content','config','flash'));
