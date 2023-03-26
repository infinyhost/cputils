<?php

namespace InfinyHost\CpUtils\Containers;

class Pod
{

    public string $Id;
    public string $Name;
    public string $Created;
    public string $ExitPolicy;
    public string $State;
    public string $Hostname;
    public bool $CreateCgroup;
    public string $CgroupPath;
    public bool $CreateInfra;
    public string $InfraContainerId;
    public int $NumContainers;
    public array $Containers;

    public function __fromArray(array $data): void
    {
        $this->Id = $data['Id'] ?? '';
        $this->Name = $data['Name'] ?? '';
        $this->Created = $data['Created'] ?? '';
        $this->ExitPolicy = $data['ExitPolicy'] ?? '';
        $this->State = $data['State'] ?? '';
        $this->Hostname = $data['Hostname'] ?? '';
        $this->CreateCgroup = $data['CreateCgroup'] ?? false;
        $this->CgroupPath = $data['CgroupPath'] ?? '';
        $this->CreateInfra = $data['CreateInfra'] ?? false;
        $this->InfraContainerId = $data['InfraContainerId'] ?? '';
        $this->NumContainers = $data['NumContainers'] ?? 0;
        $this->Containers = $data['Containers'] ?? [];
    }

    public function __fromJson(string $json): void
    {
        $data = json_decode($json, true);
        $this->__fromArray($data);
    }

    public function __toString(): string
    {
        return $this->Name;
    }

    public function __toArray(): array
    {
        return [
            'Id' => $this->Id,
            'Name' => $this->Name,
            'Created' => $this->Created,
            'ExitPolicy' => $this->ExitPolicy,
            'State' => $this->State,
            'Hostname' => $this->Hostname,
            'CreateCgroup' => $this->CreateCgroup,
            'CgroupPath' => $this->CgroupPath,
            'CreateInfra' => $this->CreateInfra,
            'InfraContainerId' => $this->InfraContainerId,
            'NumContainers' => $this->NumContainers,
            'Containers' => $this->Containers,
        ];
    }
}