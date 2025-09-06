<?php
require __DIR__ . '/../app/Bootstrap.php';

$config = require __DIR__ . '/../config/config.php';

$flash = $_SESSION['_flash'] ?? null;
unset($_SESSION['_flash']);
function flash(string $type, string $msg){ $_SESSION['_flash']=['type'=>$type,'msg'=>$msg]; }

$path = request_path();
$method = request_method();

// ------ Auth ------
if ($path === '/login') {
    if ($method === 'GET') {
        if (Auth::check()) redirect('/');
        $csrf = Auth::csrfToken($config);
        view('login', compact('csrf','config','flash'));
        exit;
    }
    if ($method === 'POST') {
        Auth::ensureCsrf($config);
        if (Auth::login(postv('user'), postv('pass'), $config)) {
            flash('ok','Bem-vindo');
            redirect('/');
        }
        flash('err','Credenciais inválidas');
        redirect('/login');
    }
}
if ($path === '/logout') { Auth::logout(); redirect('/login'); }

// ------ Enqueue via POST sem sessão (exemplo simples) ------
if ($path === '/enqueue' && $method === 'POST') {
    try {
        $type = (string)($_POST['type'] ?? '');
        $queue = (string)($_POST['queue'] ?? 'default');
        $payload = json_decode($_POST['payload'] ?? '{}', true) ?? [];
        $available = (int)($_POST['available_at'] ?? 0);
        $priority = (int)($_POST['priority'] ?? 0);
        $maxAttempts = (int)($_POST['max_attempts'] ?? 5);
        if (!$type) throw new InvalidArgumentException('type é obrigatório');
        $id = JobRepo::enqueue($type, $queue, $payload, $available, $priority, $maxAttempts);
        json_ok(['id'=>$id]);
    } catch (Throwable $e) {
        json_err($e->getMessage(), 400);
    }
}

// ------ Área logada ------
if (!Auth::check()) redirect('/login');

if ($path === '/' && $method === 'GET' && !isset($_GET['new'])) {
    $filters = [
        'status' => (string)($_GET['status'] ?? ''),
        'queue'  => (string)($_GET['queue'] ?? ''),
    ];
    $jobs = JobRepo::list($filters);
    $csrf = Auth::csrfToken($config);
    view('index', compact('config','flash','jobs','filters','csrf'));
    exit;
}

if ($path === '/' && $method === 'GET' && isset($_GET['new'])) {
    $csrf = Auth::csrfToken($config);
    view('job_form', compact('config','flash','csrf'));
    exit;
}

if ($path === '/create' && $method === 'POST') {
    Auth::ensureCsrf($config);
    try {
        $type = (string)postv('type');
        $queue = (string)postv('queue','default');
        $payload = json_decode((string)postv('payload','{}'), true) ?? [];
        $available = (int)postv('available_at', 0);
        $priority = (int)postv('priority', 0);
        $maxAttempts = (int)postv('max_attempts', 5);
        if (!$type) throw new InvalidArgumentException('type é obrigatório');
        JobRepo::enqueue($type, $queue, $payload, $available, $priority, $maxAttempts);
        flash('ok','Job enfileirado');
        redirect('/');
    } catch (Throwable $e) {
        flash('err','Erro: '.$e->getMessage());
        redirect('/?new=1');
    }
}

if ($path === '/cancel' && $method === 'POST') {
    Auth::ensureCsrf($config);
    try {
        $id = (int)postv('id');
        JobRepo::cancel($id);
        flash('ok','Job cancelado');
    } catch (Throwable $e) {
        flash('err','Erro ao cancelar: '.$e->getMessage());
    }
    redirect('/');
}

http_response_code(404);
echo 'Not Found';
