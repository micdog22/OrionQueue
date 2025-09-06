<?php
class JobRepo {
    public static function enqueue(string $type, string $queue, array $payload, int $availableAt=0, int $priority=0, int $maxAttempts=5): int {
        $now = time();
        $db = DB::conn();
        $stmt = $db->prepare("INSERT INTO jobs(type,queue,payload,status,priority,attempts,max_attempts,available_at,reserved_at,created_at,updated_at)
                              VALUES(:t,:q,:p,'queued',:pri,0,:ma,:av,NULL,:c,:u)");
        $stmt->bindValue(':t', $type, SQLITE3_TEXT);
        $stmt->bindValue(':q', $queue, SQLITE3_TEXT);
        $stmt->bindValue(':p', json_encode($payload, JSON_UNESCAPED_UNICODE), SQLITE3_TEXT);
        $stmt->bindValue(':pri', $priority, SQLITE3_INTEGER);
        $stmt->bindValue(':ma', $maxAttempts, SQLITE3_INTEGER);
        $stmt->bindValue(':av', $availableAt ?: $now, SQLITE3_INTEGER);
        $stmt->bindValue(':c', $now, SQLITE3_INTEGER);
        $stmt->bindValue(':u', $now, SQLITE3_INTEGER);
        $stmt->execute();
        return (int)$db->lastInsertRowID();
    }

    public static function findReady(string $queue='default'): ?array {
        $db = DB::conn();
        $now = time();
        $stmt = $db->prepare("
            SELECT * FROM jobs
            WHERE status='queued' AND queue=:q AND available_at <= :now
            ORDER BY priority DESC, id ASC
            LIMIT 1
        ");
        $stmt->bindValue(':q', $queue, SQLITE3_TEXT);
        $stmt->bindValue(':now', $now, SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    public static function markRunning(int $id): void {
        $db = DB::conn();
        $now = time();
        $db->exec("UPDATE jobs SET status='running', reserved_at=$now, updated_at=$now WHERE id=$id");
    }

    public static function markDone(int $id): void {
        $db = DB::conn();
        $now = time();
        $db->exec("UPDATE jobs SET status='done', updated_at=$now WHERE id=$id");
    }

    public static function markFailed(int $id, string $error, int $nextAvailable): void {
        $db = DB::conn();
        $now = time();
        $stmt = $db->prepare("UPDATE jobs SET status='queued', attempts=attempts+1, last_error=:e, available_at=:av, updated_at=:u WHERE id=:id");
        $stmt->bindValue(':e', $error, SQLITE3_TEXT);
        $stmt->bindValue(':av', $nextAvailable, SQLITE3_INTEGER);
        $stmt->bindValue(':u', $now, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public static function markPermanentlyFailed(int $id, string $error): void {
        $db = DB::conn();
        $now = time();
        $stmt = $db->prepare("UPDATE jobs SET status='failed', last_error=:e, updated_at=:u WHERE id=:id");
        $stmt->bindValue(':e', $error, SQLITE3_TEXT);
        $stmt->bindValue(':u', $now, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public static function cancel(int $id): void {
        $db = DB::conn();
        $now = time();
        $db->exec("UPDATE jobs SET status='canceled', updated_at=$now WHERE id=$id");
    }

    public static function getById(int $id): ?array {
        $db = DB::conn();
        $stmt = $db->prepare("SELECT * FROM jobs WHERE id=:id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    public static function list(array $filters=[]): array {
        $db = DB::conn();
        $w = []; $p = [];
        if (!empty($filters['status'])) { $w[] = "status=:s"; $p[':s'] = $filters['status']; }
        if (!empty($filters['queue']))  { $w[] = "queue=:q";  $p[':q'] = $filters['queue']; }
        $sql = "SELECT * FROM jobs" . (count($w) ? " WHERE " . implode(" AND ", $w) : "") . " ORDER BY id DESC LIMIT 200";
        $stmt = $db->prepare($sql);
        foreach ($p as $k=>$v) $stmt->bindValue($k, $v, SQLITE3_TEXT);
        $res = $stmt->execute();
        $out = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $out[] = $row;
        return $out;
    }
}
