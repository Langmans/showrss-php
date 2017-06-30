<?php

namespace rubenvincenten\ShowRss;

class Config
{
    private $sConfigDirectory;
    private $aConfig = [];

    public function setConfigDir($sConfigLocation = __DIR__ . '/../config/config.json')
    {
        return (bool)($this->sConfigDirectory = $sConfigLocation);
    }

    public function item($sConfigItem)
    {
        if (isset($this->aConfig[$sConfigItem])) {
            return $this->aConfig[$sConfigItem];
        }

        return null;
    }

    public function read(array $default = array())
    {
        if (file_exists($this->sConfigDirectory)) {
            $config = json_decode(file_get_contents($this->sConfigDirectory), true);
            if (json_last_error()) {
                echo json_last_error_msg(), PHP_EOL;
            }
            if (!is_array($config)) {
                $config = array();
            }
        } else {
            $config = array();
        }
        $this->aConfig = $config + $default;
        if ($this->aConfig != $config) {
            $this->write();
        }
        return $this->aConfig;
    }

    public function write()
    {
        if (!is_file($this->sConfigDirectory)) {
            $dir = dirname($this->sConfigDirectory);
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }
        file_put_contents($this->sConfigDirectory, json_encode($this->aConfig, JSON_PRETTY_PRINT));
    }
}