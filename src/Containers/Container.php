<?php

namespace InfinyHost\CpUtils\Containers;

use InfinyHost\CpUtils\Interfaces\OCISpecs;
use InfinyHost\CpUtils\Podman;
use InfinyHost\CpUtils\Exceptions\ContainerNotFoundException;
use Symfony\Component\Process\Process;

class Container implements OCISpecs
{
    public string $Id;
    public string $Created;
    public string $Path;
    public array $Args;

    public string $Image;
    public string $ImageID;
    public string $ImageName;
    public string $Name;
    public int $RestartCount;
    public string $RootFs;
    public string $Pod;

    public array $Mounts;


    public ContainerState $State;

    public function quickCreate(string $name, string $image, array $args = [], array $cmd = []): Container
    {
        $process = new Process([Podman::$podman, 'container', 'run', '--name', $name, ...$args, $image, ...$cmd]);
        $process->mustRun();
        $this->Id = trim($process->getOutput());
        return $this->load($this->Id);
    }

    public function start(): void
    {
        $process = new Process([Podman::$podman, 'start', $this->Id]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Container not start-able');
        }
    }

    public function stop(): void
    {
        $process = new Process([Podman::$podman, 'stop', $this->Id]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Container not stopable');
        }
    }

    public function kill(): void
    {
        $process = new Process([Podman::$podman, 'kill', $this->Id]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Container not killable');
        }
    }

    public function remove(): void
    {
        $process = new Process([Podman::$podman, 'rm', $this->Id]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Container not removable');
        }
    }

    public function load(string $container): Container
    {
        $process = new Process([Podman::$podman, 'inspect', '--type', 'container', $container]);
        $process->run();
        if ($process->isSuccessful()) {
            $this->__fromJson($process->getOutput());
        } else {
            throw new ContainerNotFoundException('Container not inspect-able');
        }
        return $this;
    }

    public function __fromArray(array $data): void
    {
        $this->Id = $data['Id'];
        $this->Created = $data['Created'];
        $this->Path = $data['Path'];
        $this->Args = $data['Args'];
        $this->Mounts = $data['Mounts'] ?? [];
        $this->Image = $data['Image'] ?? '';
        $this->ImageID = $data['ImageID'] ?? '';
        $this->ImageName = $data['ImageName']   ?? '';
        $this->Name = $data['Name'] ?? '';
        $this->RestartCount = $data['RestartCount'] ?? 0;
        $this->RootFs = $data['RootFs'] ?? '';
        $this->Pod = $data['Pod'] ?? '';

        $this->State = new ContainerState();
        $this->State->__fromArray($data['State']);

        $this->NetworkSettings = new NetworkSettings();
        $this->NetworkSettings->__fromArray($data['NetworkSettings']);
    }

    public function __fromJson(string $json): void
    {
        $data = json_decode($json, true);
        if (is_array($data)) {
            $data = $data[0];
        }
        $this->__fromArray($data);
    }

    public function __toString(): string
    {
        return $this->Id;
    }

    public function __toArray(): array
    {
        return [
            'Id' => $this->Id,
            'Created' => $this->Created,
            'Path' => $this->Path,
            'Args' => $this->Args,
            'Image' => $this->Image,
            'ImageID' => $this->ImageID,
            'ImageName' => $this->ImageName,
            'Name' => $this->Name,
            'RestartCount' => $this->RestartCount,
            'RootFs' => $this->RootFs,
            'Pod' => $this->Pod,
            'State' => $this->State->__toArray(),
        ];
    }

    public function __fromContainer(string $container): void
    {
        // TODO: Implement __fromContainer() method.
    }

    public function jsonSerialize()
    {
        return $this->__toArray();
    }
}