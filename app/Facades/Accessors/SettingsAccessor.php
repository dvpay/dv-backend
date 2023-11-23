<?php

namespace App\Facades\Accessors;

use App\Container\SettingsContainer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\ForwardsCalls;
use BadMethodCallException;
use Error;

class SettingsAccessor
{
    use ForwardsCalls;

    /**
     * @var SettingsContainer
     */
    protected SettingsContainer $settings;

    /**
     * @var array
     */
    protected array $scopedSettings = [];

    /**
     * @param Model|null $model
     */
    public function __construct(?Model $model = null)
    {
        $this->settings = new SettingsContainer();

        if ($model !== null) {
            $this->scopeGlobal($model);
        }
    }

    /**
     * @throws \Exception
     */
    public function scope(Model $model): SettingsContainer
    {
        $scoped = $this->scopedSettings[get_class($model)] ?? null;

        if ($scoped && $scoped->isScopedTo($model)) {
            return $scoped;
        }

        return new SettingsContainer($model);
    }

    /**
     * @param Model $model
     * @return SettingsContainer
     * @throws \Exception
     */
    public function scopeGlobal(Model $model): SettingsContainer
    {
        return $this->scopedSettings[get_class($model)] = new SettingsContainer($model, true);
    }

    /**
     * @param $method
     * @param $arguments
     * @return SettingsContainer|mixed
     * @throws \ReflectionException
     */
    public function __call($method, $arguments)
    {
        try {
            return $this->forwardCallTo($this->settings, $method, $arguments);
        } catch (Error|BadMethodCallException $e) {
            foreach ($this->scopedSettings as $class => $container) {
                $shortName = (new \ReflectionClass($class))->getShortName();

                if (strtolower($shortName) === $method) {
                    return $this->scopedSettings[$class];
                }
            }

            if (!empty($arguments) && is_object($arguments[0]) && $arguments[0] instanceof Model) {
                return $this->scope($arguments[0]);
            }

            throw new \Exception('Tried to access scope which isn\'t initialized');
        }
    }
}