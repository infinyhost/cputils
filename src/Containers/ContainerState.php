<?php

namespace InfinyHost\CpUtils\Containers;

use JsonSerializable;

class ContainerState implements \InfinyHost\CpUtils\Interfaces\OCISpecs
{
    public string $OciVersion;
    public string $Status;
    public int $Pid;
    public string $StartedAt;
    public string $FinishedAt;
    public int $ExitCode;
    public string $Error;
    public bool $OOMKilled;
    public bool $Dead;
    public bool $Paused;
    public bool $Restarting;
    public bool $Running;
    public bool $Exited;

    public function __fromArray(array $data): void
    {
        $this->OciVersion = $data['OciVersion'];
        $this->Status = $data['Status'];
        $this->Pid = $data['Pid'];
        $this->StartedAt = $data['StartedAt'];
        $this->FinishedAt = $data['FinishedAt'];
        $this->ExitCode = $data['ExitCode'];
        $this->Error = $data['Error'];
        $this->OOMKilled = $data['OOMKilled'];
        $this->Dead = $data['Dead'];
        $this->Paused = $data['Paused'];
        $this->Restarting = $data['Restarting'];
        $this->Running = $data['Running'];
    }

    public function __fromJson(string $json): void
    {
        $data = json_decode($json, true);
        $this->__fromArray($data['State']);
    }

    public function __toString(): string
    {
        return $this->Status;
    }

    public function __toArray(): array
    {
        return [
            'OciVersion' => $this->OciVersion,
            'Status' => $this->Status,
            'Pid' => $this->Pid,
            'StartedAt' => $this->StartedAt,
            'FinishedAt' => $this->FinishedAt,
            'ExitCode' => $this->ExitCode,
            'Error' => $this->Error,
            'OOMKilled' => $this->OOMKilled,
            'Dead' => $this->Dead,
            'Paused' => $this->Paused,
            'Restarting' => $this->Restarting,
            'Running' => $this->Running,
            'Exited' => $this->Exited,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->__toArray();
    }

    public function __fromContainer(string $container): void
    {
        // TODO: Implement __fromContainer() method.
    }
}