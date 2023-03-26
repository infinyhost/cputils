<?php

namespace InfinyHost\CpUtils\Tests\Unit;

class ContainerTest extends \PHPUnit\Framework\TestCase
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

    public function testContainerDoesNotLoad(): void
    {
        $container = new \InfinyHost\CpUtils\Containers\Container();
        $this->expectException(\InfinyHost\CpUtils\Exceptions\ContainerNotFoundException::class);
        $container->load('fedora-non-existing');
    }

    public function testContainerQuickCreate(): void
    {
        $container = new \InfinyHost\CpUtils\Containers\Container();
        $container = $container->quickCreate('test', 'fedora', ['-dt','--rm'], ["/usr/bin/sleep", "10"]);
        $this->assertEquals($container->Name, 'test');
    }

}