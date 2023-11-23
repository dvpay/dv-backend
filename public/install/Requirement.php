<?php

class Requirement
{
    private $result = [];
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getPhpVersion()
    {
        $version = PHP_VERSION;
        preg_match("#^\d+(\.\d+)*#", $version, $filtered);

        $currentVersion = $filtered[0];

        return [
            'full'    => $version,
            'version' => $currentVersion,
        ];
    }

    public function checkPhpVersion($minPhpVersion = null)
    {
        $minVersionPhp = $minPhpVersion ?? $this->getMinPhpVersion();
        $currentPhpVersion = $this->getPhpVersion();
        $supported = version_compare($currentPhpVersion['version'], $minVersionPhp) >= 0;

        return [
            'full'      => $currentPhpVersion['full'],
            'current'   => $currentPhpVersion['version'],
            'minimum'   => $minVersionPhp,
            'supported' => $supported,
        ];
    }

    public function getMinPhpVersion()
    {
        return $this->config->core->min_php_version;
    }

    public function check($requirements = [])
    {
        $requirements = $requirements ?: (array)$this->config->requirements;
        $requirements = (array)$requirements;
        foreach ($requirements as $type => $requirement) {
            switch ($type) {
                case 'php':
                    $this->checkPhpRequirements($requirements, $type);
                    break;
                case 'composer':
                    $this->checkComposerInstall();
            }
        }

        return $this->result;
    }

    public function checkPhpRequirements($requirements, $type)
    {
        foreach ($requirements[$type] as $requirement) {
            $this->result['requirements'][$type][$requirement] = true;

            if (!extension_loaded($requirement)) {
                $this->result['requirements'][$type][$requirement] = false;
                $this->result['errors'] = true;
            }
        }
    }

    public function checkComposerInstall()
    {
        $composerAutoloader =  '../../vendor/autoload.php';

        if (file_exists($composerAutoloader)) {
            $this->result['requirements']['composer']['install'] = true;
        } else {
            $this->result['requirements']['composer']['install'] = false;

            $this->result['errors'] = true;
        }
    }

    public function isSupported()
    {
        if ($this->checkPhpVersion()['supported']) {
            $requirements = array_filter($this->check()['requirements']['php'], function ($r) {
                return !$r;
            });
            return !count($requirements);
        }
        return false;
    }
}
