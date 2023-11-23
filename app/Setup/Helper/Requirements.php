<?php
declare(strict_types=1);

namespace App\Setup\Helper;

/**
 *
 */
class Requirements
{
    /**
     * @var array
     */
    protected $results = [];

    /**
     * @return array
     */
    public function getPhpVersion(): array
    {
        $version = PHP_VERSION;
        preg_match("#^\d+(\.\d+)*#", $version, $filtered);
        $currentVersion = $filtered[0];

        return [
            'full' => $version,
            'version' => $currentVersion,
        ];
    }

    /**
     * @param string|null $minPhpVersion
     * @return array
     */
    public function checkPhpVersion(string $minPhpVersion = null): array
    {
        $minVersionPhp = $minPhpVersion;
        $currentPhpVersion = $this->getPhpVersion();
        $supported = false;

        if ($minPhpVersion == null) {
            $minVersionPhp = $this->getMinPhpVersion();
        }

        if (version_compare($currentPhpVersion['version'], $minVersionPhp) >= 0) {
            $supported = true;
        }

        return [
            'full' => $currentPhpVersion['full'],
            'current' => $currentPhpVersion['version'],
            'minimum' => $minVersionPhp,
            'supported' => $supported,
        ];
    }

    /**
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    public function getMinPhpVersion()
    {
        return config('installer.core.minPhpVersion');
    }

    /**
     * @param array $requirements
     * @return array
     */
    public function check(array $requirements = [])
    {
        $requirements = count($requirements) ? $requirements : config('installer.requirements');

        foreach ($requirements as $type => $requirement) {
            switch ($type) {
                case 'php':
                    $this->checkPhpRequirements($requirements, $type);
                    break;
            }
        }
        return $this->results;
    }

    /**
     * @param $requirements
     * @param $type
     * @return array
     */
    public function checkPhpRequirements($requirements, $type)
    {
        foreach ($requirements[$type] as $requirement) {
            $this->results['requirements'][$type][$requirement] = true;

            if (! extension_loaded($requirement)) {
                $this->results['requirements'][$type][$requirement] = false;

                $this->results['errors'] = true;
            }
        }
        return $this->results;
    }

    /**
     * @return bool
     */
    public function isSupported(): bool
    {
        if ($this->checkPhpVersion()['supported']) {
            $requirements = collect($this->check()['requirements']['php'])->filter(function ($r) {
                return !$r;
            });
            return !$requirements->count();
        }
        return false;
    }
}
