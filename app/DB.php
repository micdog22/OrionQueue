<?php
class DB {
    private static ?SQLite3 $db = null;

    public static function init(string $path): void {
        self::$db = new SQLite3($path);
        self::$db->enableExceptions(true);
        self::$db->exec('PRAGMA foreign_keys = ON;');
        self::$db->exec('PRAGMA journal_mode = WAL;');
    }

    public static function conn(): SQLite3 {
        return self::$db;
    }

    public static function migrate(): void {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS jobs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  type TEXT NOT NULL,               -- http | php | command
  queue TEXT NOT NULL DEFAULT 'default',
  payload TEXT NOT NULL DEFAULT '',
  status TEXT NOT NULL DEFAULT 'queued', -- queued | running | done | failed | canceled
  priority INTEGER NOT NULL DEFAULT 0,
  attempts INTEGER NOT NULL DEFAULT 0,
  max_attempts INTEGER NOT NULL DEFAULT 5,
  available_at INTEGER NOT NULL DEFAULT 0,
  reserved_at INTEGER DEFAULT NULL,
  created_at INTEGER NOT NULL,
  updated_at INTEGER NOT NULL,
  last_error TEXT DEFAULT NULL
);

CREATE INDEX IF NOT EXISTS idx_jobs_ready
ON jobs(status, available_at, priority);

SQL;
        self::$db->exec($sql);
    }
}
