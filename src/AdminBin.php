<?php

namespace InfinyHost\CpUtils;

class AdminBin
{
    protected array $callable = [];
    protected bool $debug = false;
    protected array $result = [];
    const STREAM_READ = 'php://stdin';

    public function __construct() {
        $this->result = [
            'status' => true,
            'error' => null,
            'data' => null,
        ];
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * @param $errno integer error number
     * @param $errstr string error message
     * @param $errfile string file where error occurred
     * @param $errline integer line number where error occurred
     * @return void
     */
    public function errorHandler($errno=0, $errstr ='', $errfile ='', $errline=0)
    {
        $this->result['status'] = false;
        $this->result['error'] = $errstr;
        if ($this->debug) {
            $this->result['error'] .= ' in ' . $errfile . ' on line ' . $errline;
        }
        $this->result['data'] = null;
        $this->output();
        exit(1);
    }

    /**
     * @param $e \Exception exception
     * @return void
     */
    public function exceptionHandler(\Exception $e)
    {
        $this->result['status'] = false;
        $this->result['error'] = $e->getMessage();
        if ($this->debug) {
            $this->result['error'] .= ' in ' . $e->getFile() . ' on line ' . $e->getLine();
        }
        $this->result['data'] = null;
        $this->output();
        exit(1);
    }

    /**
     * Parse the input and return the data as array
     * @param $stream
     * @return array
     */
    public function parseInput($stream = self::STREAM_READ): array
    {
        return [$this->parseUserData(), $this->parseInputData($stream)];
    }

    /**
     * Parse the input data and return the data as array
     * @param $stream
     * @return array
     */
    public function parseInputData($stream = self::STREAM_READ): array
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
            throw new \RuntimeException('Invalid AdminBin command provided: ' . $request['command']);
        }

        $request['data'] = json_decode(base64_decode($stdin[1]));
        if ($request['data'] == null) {
            throw new \RuntimeException('Failed parsing AdminBin input');
        }
        return $request;
    }

    /**
     * Parse the user data and return the data as array.
     * Includes UID, GID, Username, Name and Home folder path of the user
     * @return array
     */
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

    /**
     * Fails the request and outputs the error message
     * @param string $msg
     * @return void
     */
    public function fail(string $msg='Failed executing AdminBin command'): void
    {
        $this->result['status'] = false;
        $this->result['error'] = $msg;
        $this->result['data'] = null;
        $this->output();
        exit(1);
    }

    /**
     * Succeeds the request and outputs the data
     * @param $data
     * @return void
     */
    public function success($data = null): void
    {
        $this->result['status'] = true;
        $this->result['error'] = null;
        $this->result['data'] = $data;
        $this->output();
        exit(0);
    }

    /**
     * Sets the callable commands
     * @param array $callable
     * @return void
     */
    public function setCallable(array $callable): void
    {
        $this->callable = $callable;
    }

    /**
     * Adds a callable command
     * @param string $callable
     * @return void
     */
    public function addCallable(string $callable): void
    {
        $this->callable[] = $callable;
    }

    /**
     * @param bool $debug
     * @return void
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Sets the final output to use
     * @param array $result
     * @return void
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    /**
     * Outputs the result to stdout
     * @return void
     */
    public function output(): void
    {
        echo base64_encode(json_encode($this->result));
    }
}