<?php

namespace InfinyHost\CpUtils\Containers;

class NetworkSettings implements \InfinyHost\CpUtils\Interfaces\OCISpecs
{
    public string $EndpointID;
    public string $Gateway;
    public string $IPAddress;
    public int $IPPrefixLen;
    public string $IPv6Gateway;
    public string $GlobalIPv6Address;
    public int $GlobalIPv6PrefixLen;
    public string $MacAddress;
    public string $Bridge;
    public string $SandBoxID;
    public bool $HairpinMode;
    public array $Ports;
    public string $LinkLocalIPv6Address;
    public int $LinkLocalIPv6PrefixLen;
    public string $SandboxKey;

    public array $Networks;

    public function getNetwork(string $name): array
    {
        return $this->Networks[$name];
    }

    public function ip(string $network = ''): string
    {
        if ($network === '' && $this->IPAddress !== '') {
            return $this->IPAddress;
        }

        return $this->Networks[$network]->IPAddress;
    }
    public function __fromArray(array $data): void
    {
        $this->EndpointID = $data['EndpointID'] ?? '';
        $this->Gateway = $data['Gateway'] ?? '';
        $this->IPAddress = $data['IPAddress'] ?? '';
        $this->IPPrefixLen = $data['IPPrefixLen'] ?? 0;
        $this->IPv6Gateway = $data['IPv6Gateway'] ?? '';
        $this->GlobalIPv6Address = $data['GlobalIPv6Address'] ?? '';
        $this->GlobalIPv6PrefixLen = $data['GlobalIPv6PrefixLen'] ?? 0;
        $this->MacAddress = $data['MacAddress'] ?? '';
        $this->Bridge = $data['Bridge'] ?? '';
        $this->SandBoxID = $data['SandBoxID'] ?? '';
        $this->HairpinMode = $data['HairpinMode'] ?? false;
        $this->Ports = $data['Ports'] ?? [];
        $this->LinkLocalIPv6Address = $data['LinkLocalIPv6Address'] ?? '';
        $this->LinkLocalIPv6PrefixLen = $data['LinkLocalIPv6PrefixLen'] ?? 0;
        $this->SandboxKey = $data['SandboxKey'] ?? '';
        $this->Networks = $data['Networks'] ?? [];
    }

    public function __fromJson(string $json): void
    {
        $data = json_decode($json, true);
        $this->__fromArray($data['NetworkSettings']);
    }

    public function __toString(): string
    {
        return $this->IPAddress;
    }

    public function __toArray(): array
    {
        return [
            'EndpointID' => $this->EndpointID,
            'Gateway' => $this->Gateway,
            'IPAddress' => $this->IPAddress,
            'IPPrefixLen' => $this->IPPrefixLen,
            'IPv6Gateway' => $this->IPv6Gateway,
            'GlobalIPv6Address' => $this->GlobalIPv6Address,
            'GlobalIPv6PrefixLen' => $this->GlobalIPv6PrefixLen,
            'MacAddress' => $this->MacAddress,
            'Bridge' => $this->Bridge,
            'SandBoxID' => $this->SandBoxID,
            'HairpinMode' => $this->HairpinMode,
            'Ports' => $this->Ports,
            'LinkLocalIPv6Address' => $this->LinkLocalIPv6Address,
            'LinkLocalIPv6PrefixLen' => $this->LinkLocalIPv6PrefixLen,
            'SandboxKey' => $this->SandboxKey,
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