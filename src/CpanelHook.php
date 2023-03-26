<?php

namespace InfinyHost\CpUtils;

class CpanelHook {
    public static function handle($fn): array
    {
        $input = json_decode(file_get_contents('php://stdin'), true);
        if (!$input or !is_array($input)) {
            throw new \InvalidArgumentException('Invalid input');
        }
        return $fn($input);
    }
}