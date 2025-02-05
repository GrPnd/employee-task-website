<?php

namespace icd0007tpl {

    spl_autoload_register(function ($class) {
        $prefix = 'Latte\\';
        $baseDir = __DIR__ . '/latte/src/Latte/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);

        // Special handling for exceptions
        if (matches($relativeClass, ['RuntimeException',
            'SecurityViolationException',
            'CompileException'])) {

            $file = $baseDir . 'exceptions.php';
        } else {
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        }

        if (file_exists($file)) {
            require_once $file;
        }
    });

    function matches(string $subject, array $targets): bool {
        foreach ($targets as $target) {
            if (strpos($subject, $target) !== false) {
                return true;
            }
        }

        return false;
    }



}
