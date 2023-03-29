<?php

namespace InfinyHost\CpUtils\Containers;
use InfinyHost\CpUtils\Podman;
use Symfony\Component\Process\Process;

class Builder
{

    public array $args = [];

    // The default action. One of: run, create, start, stop, restart, kill, rm, rmi, pause, unpause,
    // ps, inspect, top, stats, attach, wait, init, info, version, help
    public string $action = 'run';
    public string $image = '';
    public string $rootfs = '';
    public string $name = '';
    public array $command = [];

    // This is the subject we are working on
    public string $subject = 'container';

    // The default network name
    public string $network = 'podman';

    // How do we manage the cgroups
    public string $cgroup_manager = 'cgroupfs';

    public static function new(): Builder
    {
        return new Builder();
    }

    /**
     * Set the cgroup manager. One of: cgroupfs, systemd
     * @param string $manager
     * @return $this
     */
    public function cgroupManager(string $manager): Builder
    {
        $this->cgroup_manager = $manager;
        return $this;
    }

    /**
     * Set the podman action command. One of: run, create, start, stop, restart, kill, rm, rmi, pause, unpause
     * @param string $action
     * @return $this
     */
    public function action(string $action): Builder
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Set the subject we are working on. One of: container, pod, image, volume, network, system
     * @param string $subject
     * @return $this
     */
    public function subject(string $subject): Builder
    {
        $this->subject = $subject;
        return $this;
    }


    /**
     * Set the interactive flag for the container
     * @return $this
     */
    public function interactive(): Builder
    {
        $this->args[] = '-i';
        return $this;
    }

    /**
     * Set the tty flag for the container
     * @return $this
     */
    public function tty(): Builder
    {
        $this->args[] = '-t';
        return $this;
    }


    /**
     * Detach from the container on run
     * @return $this
     */
    public function daemon(): Builder
    {
        $this->args[] = '-d';
        return $this;
    }


    /**
     * Remove the container on exit
     * @return $this
     */
    public function remove(): Builder
    {
        $this->args[] = '--rm';
        return $this;
    }

    /**
     * Replace the container if it already exists
     * @return $this
     */
    public function replace(): Builder
    {
        $this->args[] = '--replace';
        return $this;
    }

    /**
     * Set the container name
     * @param string $name
     * @return $this
     */
    public function name(string $name): Builder
    {
        $this->args[] = '--name';
        $this->args[] = $name;
        $this->name = $name;
        return $this;
    }

    /**
     * Set the container image
     * @param string $image
     * @return $this
     */
    public function image(string $image): Builder
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Set the container command
     * @param array $command
     * @return $this
     */
    public function command(array $command): Builder
    {
        $this->command = $command;
        return $this;
    }


    /**
     * Add an environment variable to the container
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function env(string $key, string $value): Builder
    {
        $this->args[] = '-e';
        $this->args[] = $key . '=' . $value;
        return $this;
    }


    /**
     * Add a volume to the container
     * @param string $source
     * @param string $destination
     * @return $this
     */
    public function volume(string $source, string $destination): Builder
    {
        $this->args[] = '-v';
        $this->args[] = $source . ':' . $destination;
        return $this;
    }

    /**
     * Add a port to publish on the container
     * @param string $source
     * @param string $destination
     * @return $this
     */
    public function port(string $source, string $destination): Builder
    {
        $this->args[] = '-p';
        $this->args[] = $source . ':' . $destination;
        return $this;
    }

    /**
     * Set the container network
     * @param string $network
     * @return $this
     */
    public function network(string $network): Builder
    {
        $this->args[] = '--network';
        $this->args[] = $network;
        $this->network = $network;
        return $this;
    }

    /**
     * Restart the container on failure
     * @param string $restart
     * @return $this
     */
    public function restart(string $restart): Builder
    {
        $this->args[] = '--restart';
        $this->args[] = $restart;
        return $this;
    }

    /**
     * Specify the user to run the container as
     * @param string|int $user
     * @return $this
     */
    public function user($user): Builder
    {
        $this->args[] = '--user';
        $this->args[] = $user;
        return $this;
    }

    /**
     * Specify the group to run the container as
     * @param string|int $group
     * @return $this
     */
    public function group(string $group): Builder
    {
        $this->args[] = '--group';
        $this->args[] = $group;
        return $this;
    }

    /**
     * Set the working directory for the container
     * @param string $workdir
     * @return $this
     */
    public function workdir(string $workdir): Builder
    {
        $this->args[] = '--workdir';
        $this->args[] = $workdir;
        return $this;
    }

    /**
     * Find the gateway of the specified network
     * @param string $network
     * @return string
     */
    public function getNetworkGateway(string $network): string
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
            foreach ($net['plugins'] as $plugin) {
                if (isset($plugin['ipam'])) {
                    if (isset($plugin['ipam']['ranges'][0]) && isset($plugin['ipam']['ranges'][0][0]) && isset($plugin['ipam']['ranges'][0][0]['gateway'])) {
                        $range = $plugin['ipam']['ranges'][0][0]['gateway'];
                        break;
                    }
                    // Not the network we are looking for.. Move on.
                }
            }
        } else {
            throw new \RuntimeException("Failed getting network info for " . $network . " : " . $process->getErrorOutput());
        }
        return $range;
    }

    /**
     * Find the IP address of the user. This is done by subtracting $subtract (1000 by default) from the uid and adding it to the network gateway IP address.
     * @param int $uid
     * @param string $network
     * @param int $subtract
     * @return string
     */
    public function findUserIP(int $uid, string $network="", int $subtract=1000): string
    {
        // Find the user IP address by subtracting 1000 from the uid and add it to the guests IP network of 192.168.4.1/24
        // If the resulting IP address is out of range, increase the IP CIDR range by one
        // Get the network info from podman and extract the CIDR range
        if ($network == "") {
            $network = $this->network;
        }
        $range = $this->getNetworkGateway($network);

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

    /**
     * Set the IP address of the container
     * @param string $ip
     * @return $this
     */
    public function ip(string $ip): Builder
    {
        $this->args[] = '--ip';
        $this->args[] = $ip;
        return $this;
    }

    /**
     * Set the cgroup parent of the container
     * @param string $cGroupParent
     * @return $this
     */
    public function cGroupParent(string $cGroupParent): Builder
    {
        $this->args[] = '--cgroup-parent';
        $this->args[] = $cGroupParent;
        return $this;
    }

    /**
     * Set the rootfs of the container. Rootfs is the path to the exploded container on the file system.
     * @param string $rootfs
     * @return $this
     */
    public function rootfs(string $rootfs): Builder
    {
        $this->rootfs = $rootfs;
        return $this;
    }

    /**
     * Set the hostname of the container
     * @param string $hostname
     * @return $this
     */
    public function hostname(string $hostname): Builder
    {
        $this->args[] = '--hostname';
        $this->args[] = $hostname;
        return $this;
    }

    /**
     * Same as --daemon.
     * @return $this
     */
    public function detach(): Builder
    {
        $this->args[] = '--detach';
        return $this;
    }

    public function label(string $label): Builder
    {
        $this->args[] = '--label';
        $this->args[] = $label;
        return $this;
    }

    public function labelFile(string $labelFile): Builder
    {
        $this->args[] = '--label-file';
        $this->args[] = $labelFile;
        return $this;
    }

    public function logDriver(string $logDriver): Builder
    {
        $this->args[] = '--log-driver';
        $this->args[] = $logDriver;
        return $this;
    }

    public function logOpt(string $logOpt): Builder
    {
        $this->args[] = '--log-opt';
        $this->args[] = $logOpt;
        return $this;
    }

    public function memory(string $memory): Builder
    {
        $this->args[] = '--memory';
        $this->args[] = $memory;
        return $this;
    }

    public function memoryReservation(string $memoryReservation): Builder
    {
        $this->args[] = '--memory-reservation';
        $this->args[] = $memoryReservation;
        return $this;
    }

    public function memorySwap(string $memorySwap): Builder
    {
        $this->args[] = '--memory-swap';
        $this->args[] = $memorySwap;
        return $this;
    }

    public function memorySwapiness(string $memorySwapiness): Builder
    {
        $this->args[] = '--memory-swapiness';
        $this->args[] = $memorySwapiness;
        return $this;
    }

    public function kernelMemory(string $kernelMemory): Builder
    {
        $this->args[] = '--kernel-memory';
        $this->args[] = $kernelMemory;
        return $this;
    }

    public function cpuShares(string $cpuShares): Builder
    {
        $this->args[] = '--cpu-shares';
        $this->args[] = $cpuShares;
        return $this;
    }

    public function cpuPeriod(string $cpuPeriod): Builder
    {
        $this->args[] = '--cpu-period';
        $this->args[] = $cpuPeriod;
        return $this;
    }

    public function cpuQuota(string $cpuQuota): Builder
    {
        $this->args[] = '--cpu-quota';
        $this->args[] = $cpuQuota;
        return $this;
    }

    public function cpuRtPeriod(string $cpuRtPeriod): Builder
    {
        $this->args[] = '--cpu-rt-period';
        $this->args[] = $cpuRtPeriod;
        return $this;
    }

    public function cpuRtRuntime(string $cpuRtRuntime): Builder
    {
        $this->args[] = '--cpu-rt-runtime';
        $this->args[] = $cpuRtRuntime;
        return $this;
    }

    public function cpus(string $cpus): Builder
    {
        $this->args[] = '--cpus';
        $this->args[] = $cpus;
        return $this;
    }

    public function cpusetCpus(string $cpusetCpus): Builder
    {
        $this->args[] = '--cpuset-cpus';
        $this->args[] = $cpusetCpus;
        return $this;
    }

    public function cpusetMems(string $cpusetMems): Builder
    {
        $this->args[] = '--cpuset-mems';
        $this->args[] = $cpusetMems;
        return $this;
    }

    public function blkioWeight(string $blkioWeight): Builder
    {
        $this->args[] = '--blkio-weight';
        $this->args[] = $blkioWeight;
        return $this;
    }

    public function blkioWeightDevice(string $blkioWeightDevice): Builder
    {
        $this->args[] = '--blkio-weight-device';
        $this->args[] = $blkioWeightDevice;
        return $this;
    }

    public function pod(string $pod): Builder
    {
        $this->args[] = '--pod';
        $this->args[] = $pod;
        return $this;
    }

    public function build(bool $dryRun = false)
    {
        // This holds the entire command
        $cmd = [];
        // Add Podman binary
        $cmd[] = Podman::$podman;
        // Add cgroup manager
        $cmd[] = '--cgroup-manager';
        $cmd[] = Podman::$cgroupManager;
        // Add the action (run, create, start, stop)
        $cmd[] = $this->action;
        // Add the image to run
        // Merge all other arguments
        $cmd = array_merge($cmd, $this->args);

        // Add the image to run or the rootfs
        if ($this->rootfs != '') {
            $cmd[] = '--rootfs';
            $cmd[] = $this->rootfs;
        } else {
            $cmd[] = $this->image;
        }

        // Add the command to run
        $cmd = array_merge($cmd, $this->command);

        // Run the command
        if ($dryRun) {
            return $cmd;
        }
        return $this->exec($cmd);
    }

    public function exec(array $cmd): Container
    {
        $process = new Process($cmd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $container = new Container();
        $container->load(trim($process->getOutput()));
        return $container;
    }

    public function run()
    {
        $this->action = 'run';
        return $this->build();
    }

    public function create(): string
    {
        $this->action = 'create';
        return $this->build();
    }

    public function start(): string
    {
        $this->action = 'start';
        return $this->build();
    }
}