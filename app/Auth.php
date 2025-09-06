<?php
class Auth {
    public static function login(string $user, string $pass, array $config): bool {
        if ($user !== $config['admin_user']) return false;
        if (!password_verify($pass, $config['admin_pass_hash'])) return false;
        session_regenerate_id(true);
        $_SESSION['is_auth'] = true;
        $_SESSION['auth_user'] = $user;
        return true;
    }
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
    public static function check(): bool { return !empty($_SESSION['is_auth']); }
    public static function csrfToken(array $config): string {
        if (empty($_SESSION[$config['csrf_key']])) {
            $_SESSION[$config['csrf_key']] = bin2hex(random_bytes(16));
        }
        return $_SESSION[$config['csrf_key']];
    }
    public static function ensureCsrf(array $config): void {
        $t = $_POST['_csrf'] ?? '';
        if (!$t || $t !== ($_SESSION[$config['csrf_key']] ?? null)) {
            http_response_code(400); exit('CSRF inválido');
        }
    }
}
