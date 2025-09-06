<?php
class UserJobs {
    public static function allowed(): array {
        return [
            'sayHello',
            'writeLog'
        ];
    }

    public static function sayHello(string $name='World'): void {
        $msg = "Hello, " . $name . " at " . date('c') . "\n";
        file_put_contents(__DIR__ . '/../../logs/userjobs.log', $msg, FILE_APPEND);
    }

    public static function writeLog(string $content): void {
        $msg = "[UserLog] " . $content . " at " . date('c') . "\n";
        file_put_contents(__DIR__ . '/../../logs/userjobs.log', $msg, FILE_APPEND);
    }
}
