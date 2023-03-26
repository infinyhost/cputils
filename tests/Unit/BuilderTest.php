<?php

namespace InfinyHost\CpUtils\Tests\Unit;

use InfinyHost\CpUtils\Containers\Builder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function setUp(): void
    {
        set_error_handler(
            static function ( $errno, $errstr ) {
                throw new \Exception( $errstr, $errno );
            },
            E_ALL
        );
    }

    public function tearDown(): void
    {
        restore_error_handler();
    }

    public function testBuilderArrayIsCorrect(): void
    {
        $builder = new Builder();
        $builder->name('test')
            ->image('test')
            ->ip('192.168.4.101')
            ->port(80,443)
            ->volume('/var/www/htmls', '/var/www/htmld')
            ->env('TEST', 'test')
            ->user('testuser')
            ->group('testgroup')
            ->memory(1024)
            ->cpus(1)
            ->cpuShares(1024)
            ->cpuPeriod(100000)
            ->cpuQuota(50000)
            ->cpusetCpus('0-1')
            ->cpusetMems('0')
            ->blkioWeight(500)
            ->blkioWeightDevice('/dev/sda', 500)
            ->action('run')
            ->subject('container')
            ->cgroupManager('cgroupfs');

        $this->assertEquals($builder->build(true),
            array (
                0 => '/usr/bin/podman',
                1 => '--cgroup-manager',
                2 => 'cgroupfs',
                3 => 'run',
                4 => '--name',
                5 => 'test',
                6 => '--ip',
                7 => '192.168.4.101',
                8 => '-p',
                9 => '80:443',
                10 => '-v',
                11 => '/var/www/htmls:/var/www/htmld',
                12 => '-e',
                13 => 'TEST=test',
                14 => '--user',
                15 => 'testuser',
                16 => '--group',
                17 => 'testgroup',
                18 => '--memory',
                19 => '1024',
                20 => '--cpus',
                21 => '1',
                22 => '--cpu-shares',
                23 => '1024',
                24 => '--cpu-period',
                25 => '100000',
                26 => '--cpu-quota',
                27 => '50000',
                28 => '--cpuset-cpus',
                29 => '0-1',
                30 => '--cpuset-mems',
                31 => '0',
                32 => '--blkio-weight',
                33 => '500',
                34 => '--blkio-weight-device',
                35 => '/dev/sda',
                36 => 'test',
            ),
            'Builder array is not correct'
        );
    }

}