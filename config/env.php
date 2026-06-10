<?php

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new Exception(".env file not found: $path");
    }

    $lines = file(
        $path,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    foreach ($lines as $line) {

        $line = trim($line);

        if (
            $line === '' ||
            str_starts_with($line, '#')
        ) {
            continue;
        }

        $parts = explode(
            '=',
            $line,
            2
        );

        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        $_ENV[$key] = $value;

        putenv("$key=$value");
    }
}