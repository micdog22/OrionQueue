<?php
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function view(string $tpl, array $data=[]): void { extract($data); require __DIR__ . "/Views/$tpl.php"; }
function redirect(string $to): void { header("Location: $to", true, 302); exit; }
function request_path(): string { $u = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'; return rtrim($u, '/') ?: '/'; }
function request_method(): string { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
function param(string $n, $d='') { return $_GET[$n] ?? $d; }
function postv(string $n, $d='') { return $_POST[$n] ?? $d; }

function json_ok($data){ header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>true,'data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function json_err(string $msg, int $code=400){ http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE); exit; }
