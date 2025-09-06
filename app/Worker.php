<?php
class Worker {
    private string $queue;
    private array $config;
    private bool $running = true;

    public function __construct(string $queue, array $config) {
        $this->queue = $queue;
        $this->config = $config;
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function(){ $this->running = false; });
        pcntl_signal(SIGTERM, function(){ $this->running = false; });
    }

    public function run(): void {
        $this->log("Worker started on queue '{$this->queue}'");
        while ($this->running) {
            $job = JobRepo::findReady($this->queue);
            if (!$job) {
                sleep($this->config['idle_sleep']);
                continue;
            }
            $this->process($job);
        }
        $this->log("Worker stopped");
    }

    private function process(array $job): void {
        $id = (int)$job['id'];
        JobRepo::markRunning($id);
        $this->log("Processing job #$id type={$job['type']}");

        $payload = json_decode($job['payload'] ?? '{}', true) ?? [];
        $ok = false; $error = '';
        try {
            switch ($job['type']) {
                case 'http':
                    $ok = $this->handleHttp($payload, $error);
                    break;
                case 'php':
                    $ok = $this->handlePhp($payload, $error);
                    break;
                case 'command':
                    $ok = $this->handleCommand($payload, $error);
                    break;
                default:
                    $error = 'Unknown job type';
                    $ok = false;
            }
        } catch (Throwable $e) {
            $ok = false;
            $error = $e->getMessage();
        }

        if ($ok) {
            JobRepo::markDone($id);
            $this->log("Job #$id done");
            return;
        }

        // retry or fail
        $attempts = (int)$job['attempts'] + 1;
        $max = (int)$job['max_attempts'];
        if ($attempts >= $max) {
            JobRepo::markPermanentlyFailed($id, $error);
            $this->log("Job #$id failed permanently: $error");
        } else {
            $delay = $this->computeBackoff($attempts);
            $next = time() + $delay;
            JobRepo::markFailed($id, $error, $next);
            $this->log("Job #$id failed (attempt $attempts/$max). Next try in {$delay}s. Error: $error");
        }
    }

    private function handleHttp(array $p, string &$err): bool {
        $url = $p['url'] ?? '';
        if (!$url) { $err = 'Missing url'; return false; }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'OrionQueue/1.0',
        ]);
        $out = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $ce = curl_error($ch);
        curl_close($ch);
        if ($out === false || $code >= 400) {
            $err = $ce ?: ("HTTP ".$code);
            return false;
        }
        return true;
    }

    private function handlePhp(array $p, string &$err): bool {
        $fn = $p['function'] ?? '';
        $args = $p['args'] ?? [];
        require_once __DIR__ . '/Jobs/UserJobs.php';
        $allowed = UserJobs::allowed();
        if (!in_array($fn, $allowed, true)) { $err = 'Function not allowed'; return false; }
        try {
            call_user_func_array(['UserJobs',$fn], is_array($args)?$args:[]);
            return true;
        } catch (Throwable $e) {
            $err = $e->getMessage();
            return false;
        }
    }

    private function handleCommand(array $p, string &$err): bool {
        if (empty($this->config['allow_shell'])) { $err = 'Shell disabled'; return false; }
        $cmd = $p['cmd'] ?? '';
        if (!$cmd) { $err = 'Missing cmd'; return false; }
        $out = shell_exec($cmd . ' 2>&1');
        if ($out === null) { $err = 'Command failed'; return false; }
        return true;
    }

    private function computeBackoff(int $attempt): int {
        $base = (int)$this->config['backoff_base'];
        $factor = (int)$this->config['backoff_factor'];
        $jitter = (int)$this->config['backoff_jitter'];
        $delay = $base * max(1, pow($factor, $attempt-1));
        $delay += random_int(0, $jitter);
        return (int)$delay;
    }

    private function log(string $line): void {
        $file = $this->config['log_file'];
        $ts = date('Y-m-d H:i:s');
        file_put_contents($file, "[$ts] $line
", FILE_APPEND);
        echo "[$ts] $line
";
    }
}
