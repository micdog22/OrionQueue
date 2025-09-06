<?php
return [
    'app_name'      => 'OrionQueue',
    'session_name'  => 'orionqueue_sess',
    'csrf_key'      => 'orionqueue_csrf',

    'data_dir'      => __DIR__ . '/../data',
    'db_path'       => __DIR__ . '/../data/queue.sqlite',
    'logs_dir'      => __DIR__ . '/../logs',
    'log_file'      => __DIR__ . '/../logs/worker.log',

    // Credenciais do painel
    'admin_user'    => 'admin@local',
    'admin_pass_hash' => '$2y$10$J1oUrGKRGxmhpJ4uIDydeee.jQR3zeb9WClmRrx1ZV/sCCI96CUxC',

    // Comando local: por segurança, manter false em produção
    'allow_shell'   => false,

    // Backoff do worker (segundos)
    'backoff_base'  => 3,
    'backoff_factor'=> 2,
    'backoff_jitter'=> 2,

    // Polling do worker (intervalo quando não há job)
    'idle_sleep'    => 2,
];
