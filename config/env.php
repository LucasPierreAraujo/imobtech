<?php
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$chave, $valor] = explode('=', $line, 2);
        if (!getenv(trim($chave))) {
            putenv(trim($chave) . '=' . trim($valor));
        }
    }
}
