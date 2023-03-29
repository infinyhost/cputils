<?php

namespace InfinyHost\CpUtils;

use Symfony\Component\Process\Process;

class Network
{
    public static function userContainerIP(int $uid, string $network="Customers", int $subtract=1000)
    {
        $range = self::userContainerGateway($network);

        // Get the IP address of the user
        $range = ip2long($range);
        // +1 because we want to start from the second IP address, first being the gateway
        $ip = $range - $subtract + $uid + 1;
        $result = long2ip($ip);
        if ($result === false) {
            throw new \RuntimeException("Failed getting IP address for user " . $uid . " in network " . $network);
        }
        return $result;
    }

    public static function userContainerGateway(string $network)
    {
        $range = '';
        $process = new Process([Podman::$podman, 'network', 'inspect', $network]);
        $process->run();
        if ($process->isSuccessful()) {
            $network = json_decode($process->getOutput(), true);
            if ($network === false || !is_array($network) || count($network) == 0) {
                throw new \RuntimeException("Failed getting network info for " . $network . " : " . $process->getErrorOutput());
            }
            $net = $network[0];
            if (isset($net['subnets']) && isset($net['subnets'][0]) && isset($net['subnets'][0]['gateway'])) {
                $range = $net['subnets'][0]['gateway'];
            } else {
                if (!isset($net['plugins'])) {
                    throw new \RuntimeException("Failed getting network rootless info.");
                }
                foreach ($net['plugins'] as $plugin) {
                    if (isset($plugin['ipam'])) {
                        if (isset($plugin['ipam']['ranges'][0]) && isset($plugin['ipam']['ranges'][0][0]) && isset($plugin['ipam']['ranges'][0][0]['gateway'])) {
                            $range = $plugin['ipam']['ranges'][0][0]['gateway'];
                            break;
                        }
                        // Not the network we are looking for.. Move on.
                    }
                }
            }

        } else {
            throw new \RuntimeException("Failed getting network info for " . $network . " : " . $process->getErrorOutput());
        }
        if ($range === '') {
            throw new \RuntimeException("Failed getting network range info ");
        }
        return $range;
    }

}