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
            throw new PodNotFoundException('Pod not found');
        }
    }

    /**
     * @throws PodNotFoundException
     */
    public static function podCreate(string $name, array $args = []): Pod
    {
        $process = new Process([self::$podman, 'pod', 'create','--infra-name', "infra-" . $name, '--name', $name, ...$args]);
        $process->mustRun();
        return self::podInspect($name);
    }

    /**
     * Check if a pod is running
     * @param string $pod
     * @return bool
     * @throws PodNotFoundException
     */
    public static function podIsRunning(string $pod): bool
    {
        $pod = self::podInspect($pod);
        return $pod->State == 'Running';
    }

    /**
     * Start a pod
     * @param string $pod
     * @return void
     */
    public static function podStart(string $pod): void
    {
        $process = new Process([self::$podman, 'pod', 'start', $pod]);
        $process->mustRun();
    }

    /**
     * Stop a pod
     * @param string $pod
     * @return void
     */
    public static function podStop(string $pod): void
    {
        $process = new Process([self::$podman, 'pod', 'stop', $pod]);
        $process->mustRun();
    }

    /**
     * Kill a pod
     * @param string $pod
     * @return void
     */
    public static function podKill(string $pod): void
    {
        $process = new Process([self::$podman, 'pod', 'kill', $pod]);
        $process->mustRun();
    }

    /**
     * Restart a pod
     * @param string $pod
     * @return void
     */
    public static function podRestart(string $pod): void
    {
        $process = new Process([self::$podman, 'pod', 'restart', $pod]);
        $process->mustRun();
    }

    /**
     * Pause a pod
     * @param string $pod
     * @return void
     */
    public static function podPause(string $pod): void
    {
        $process = new Process([self::$podman, 'pod', 'pause', $pod]);
        $process->mustRun();
    }

    /**
     * Unpause a pod
     * @param string $pod
     * @return void
     */
    public static function podUnpause(string $pod): void
    {
        $process = new Process([self::$podman, 'pod', 'unpause', $pod]);
        $process->mustRun();
    }

    /**
     * Remove a pod
     * @param string $pod
     * @return void
     */
    public static function podRm(string $pod): void
    {
        $process = new Process([self::$podman, 'pod', 'rm', $pod]);
        $process->mustRun();
    }

    /**
     * Prune all pods
     * @return void
     */
    public static function podPrune(): void
    {
        $process = new Process([self::$podman, 'pod', 'prune']);
        $process->mustRun();
    }



}