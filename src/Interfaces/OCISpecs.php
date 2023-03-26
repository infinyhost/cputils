<?php

namespace InfinyHost\CpUtils\Interfaces;

interface OCISpecs
{
    public function __toArray(): array;
    public function __fromArray(array $data): void;
    public function __fromJson(string $json): void;
    public function __fromContainer(string $container): void;
    public function __toString(): string;
}