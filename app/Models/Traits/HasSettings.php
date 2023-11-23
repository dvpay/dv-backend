<?php

namespace App\Models\Traits;

use App\Container\SettingsContainer;
use App\Facades\Settings;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSettings
{
    use HasSettingsDefinitions;

    /**
     * @var ?SettingsContainer
     */
    protected ?SettingsContainer $settingsContainer = null;

    /**
     * @return MorphMany
     */
    public function settingsRelation (): MorphMany
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    /**
     * @return SettingsContainer
     */
    public function getSettingsAttribute (): SettingsContainer
    {
        return Settings::scope($this);
    }

}