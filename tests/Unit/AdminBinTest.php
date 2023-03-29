<?php

namespace InfinyHost\CpUtils\Tests\Unit;



class AdminBinTest extends \PHPUnit\Framework\TestCase
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

    public function testUserParse(): void
    {
        $_SERVER['argv'] = ['test', posix_getuid()];
        $adm = new \InfinyHost\CpUtils\AdminBin();
        $this->assertEquals($adm->parseUserData(), [
            'uid' => posix_getuid(),
            'gid' => posix_getgid(),
            'username' => posix_getpwuid(posix_getuid())['name'],
            'home' => posix_getpwuid(posix_getuid())['dir'],
        ]);
    }

    public function testStdinParseNoSpace(): void
    {
        $adm = new \InfinyHost\CpUtils\AdminBin();
        $this->expectExceptionMessage('Failed exploding AdminBin input');
        $adm->paarseInputData(__DIR__ . "/../Data/AdminBin/nospace.txt");
    }

    public function testStdinParseNoData(): void
    {
        $adm = new \InfinyHost\CpUtils\AdminBin();
        $this->expectExceptionMessageMatches('/ Failed to open stream\: No such file or directory/i');
        $adm->paarseInputData(__DIR__ . "/../Data/AdminBin/nospaces.txt");
    }

    public function testStdinParse(): void
    {
        $adm = new \InfinyHost\CpUtils\AdminBin();
        $adm->setCallable('correct_function');
        $cls =  base64_encode(json_encode(['test' => 'test']));
        $this->assertEquals($adm->paarseInputData(__DIR__ . "/../Data/AdminBin/correct.txt"), [
            'command' => 'correct_function',
            'data' => json_decode(base64_decode($cls), ),
        ]);
    }

    public function testPublicFunctionDeny(): void
    {
        $adm = new \InfinyHost\CpUtils\AdminBin();
        $this->expectExceptionMessage('Invalid AdminBin command');
        $adm->paarseInputData(__DIR__ . "/../Data/AdminBin/correct.txt");
    }

    public function testHandle(): void
    {
        $this->assertTrue(true);
    }

    public function testHandleException(): void
    {
        $this->assertTrue(true);
    }

}