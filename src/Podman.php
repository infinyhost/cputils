<?php

namespace InfinyHost\CpUtils;

use InfinyHost\CpUtils\Containers\Container;
use InfinyHost\CpUtils\Containers\Pod;
use InfinyHost\CpUtils\Exceptions\ContainerNotFoundException;
use InfinyHost\CpUtils\Exceptions\PodNotFoundException;
use Symfony\Component\Process\Process;

class Podman
{
    public static string $podman = '/usr/bin/podman';
    public static string $cgroupManager = 'cgroupfs';
    public static string $podmanCompose = '/usr/bin/podman-compose';

    public static function containerExists(string $container): bool
    {
        $process = new Process([self::$podman, 'container', 'exists', $container]);
        $process->run();
        return $process->isSuccessful();
    }

    public static function inspect(string $container): Container
    {
        $process = new Process([self::$podman, 'inspect', '--type', 'container', $container]);
        $process->run();
        if ($process->isSuccessful()) {
            $container = new Container();
            $container->__fromJson($process->getOutput());
            return $container;
        } else {
            throw new ContainerNotFoundException('Container not found');
        }
    }

    public static function podExists(string $pod): bool
    {
        $process = new Process([self::$podman, 'pod', 'exists', $pod]);
        $process->run();
        return $process->isSuccessful();
    }

    public static function podInspect(string $pod): Pod
    {
        $process = new Process([self::$podman, 'pod', 'inspect', $pod]);
        $process->run();
        if ($process->isSuccessful()) {
            $pod = new Pod();
            $pod->__fromJson($process->getOutput());
            return $pod;
        } else {
            throw new PodNotFoundException('Container not found');
        }
    }


}