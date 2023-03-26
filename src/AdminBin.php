<?php

namespace InfinyHost\CpUtils;

class AdminBin
{
    public $callable = [];
    const STREAM_READ = 'php://stdin';

    public function parseInput($stream = self::STREAM_READ): array
    {
        return [$this->parseUserData(), $this->paarseInputData($stream)];
    }

    public function paarseInputData($stream = self::STREAM_READ): array
    {
        $request = [
            'command' => '',
            'data' => null,
        ];
        // Parse the stdin input
        $stdin = trim(@file_get_contents($stream));
        if (!$stdin || $stdin == "") {
            throw new \RuntimeException('Failed parsing AdminBin input from stream');
        }

        // $stdin is a string with the first word being the function to execute and the rest being the base64 encoded data
        $stdin = explode(' ', $stdin,2);
        if ($stdin === false || count($stdin) != 2) {
            throw new \RuntimeException('Failed exploding AdminBin input');
        }

        $request['command'] = $stdin[0];
        if (!in_array($request['command'], $this->callable)) {
            throw new \RuntimeException('Invalid AdminBin command');
        }

        $request['data'] = json_decode(base64_decode($stdin[1]));
        if ($request['data'] == null) {
            throw new \RuntimeException('Failed parsing AdminBin input');
        }
        return $request;
    }

    public function parseUserData(): array
    {
        $data = [
            'uid' => -1,
            'gid' => -1,
            'username' => '',
            'home' => '',
        ];

        if (isset($_SERVER['argv']) && is_array($_SERVER['argv']) && count($_SERVER['argv']) > 1) {
            $data['uid'] = (int)$_SERVER['argv'][1];
        }

        // Allow only UIDS above 1000
        if ($data['uid'] < 1000) {
            throw new \RuntimeException('Invalid AdminBin uid');
        }

        // If we have a UID, get the username
        $user_info = posix_getpwuid($data['uid']);
        if ($user_info === false) {
            throw new \RuntimeException('Failed parsing AdminBin uid');
        }

        // Get data from user info
        if (is_array($user_info) && isset($user_info['name'])) {
            $data['username'] = $user_info['name'];
            $data['home'] = $user_info['dir'];
            $data['gid'] = $user_info['gid'];
        }

        return $data;
    }
}