<?php

namespace InfinyHost\CpUtils;

use BadMethodCallException;
use RuntimeException;

class AdminClient
{
    private $cpanel;

    public function __construct($cpanel)
    {
        $this->cpanel = $cpanel;
    }

    /**
     * Calls a cPanel adminbin function
     * @param string $module
     * @param array $args
     * @param string $function
     * @return array
     */
    public function call(string $module, array $args = [], string $function ="wrapper"): array
    {
        $result = $this->cpanel->uapi($module, $function, [ 'params' => base64_encode(json_encode($args))]);
        if (!is_array($result) || !isset($result['cpanelresult']) or !isset($result['cpanelresult']["result"])) {
            throw new BadMethodCallException("Invalid response from cPanel");
        }
        $result = $result['cpanelresult']["result"];
        if (isset($result["errors"]) && count($result["errors"]) > 0) {
            throw new RuntimeException($result["errors"][0]);
        }

        if (!isset($result["data"]) || $result["data"] == null || $result["data"] == "") {
            throw new RuntimeException("Invalid data response");
        }
        $data = json_decode(base64_decode($result["data"]), true);
        if (!is_array($data)) {
            throw new RuntimeException("Invalid data response");
        }
        return $data;
    }
}