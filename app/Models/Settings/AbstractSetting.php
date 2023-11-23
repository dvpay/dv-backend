<?php

namespace App\Models\Settings;

use App\Models\Traits\CastsToType;
use App\Models\Traits\HasSettingsDefinitions;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

abstract class AbstractSetting extends Model
{
    use CastsToType,
        HasSettingsDefinitions;

    protected $table = 'settings';

    protected $fillable = [
        'model_id',
        'model_type',
        'name',
        'value',
    ];

    protected $casts = [
        'model_id' => 'integer',
    ];

    public function settingable(): MorphTo
    {
        return $this->morphTo('settingsRelation');
    }

    public function value(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $definition = static::getSettingDefinition($attributes['name']);

                if ($attributes['model_type']) {
                    $definition = $attributes['model_type']::getSettingDefinition($attributes['name']);
                }

                if (empty($definition['cast'])) {
                    return $value;
                }

                return $this->castToType($definition['cast'], $value);
            },
            set: function ($value, $attributes) {
                $definition = self::getSettingDefinition($attributes['name']);

                if ($attributes['model_type']) {
                    $definition = $attributes['model_type']::getSettingDefinition($attributes['name']);
                }

                if (empty($definition['cast'])) {
                    return $value;
                }

                $this->setAttributeByType($definition['cast'], 'value', $value);

                return $this->attributes['value'];
            }
        );
    }

    public static function getSettingDefinition(string $name): ?array
    {
        $definitions = static::getSettingsDefinitions();

        foreach ($definitions as $definition) {
            if ($definition['name'] === $name) {
                return $definition;
            }
        }

        return null;
    }

    public static function getGlobalSettingsDefinitions(): array
    {
        return static::getSettingsDefinitions();
    }

    protected function mergeAttributesFromClassCasts(): void
    {
        foreach ($this->classCastCache as $key => $value) {
            if ($key === 'value') {
                $definition = self::getSettingDefinition($this->attributes['name']);

                if ($this->attributes['settingable_type']) {
                    $definition = $this->attributes['settingable_type']::getSettingDefinition($this->attributes['name']);
                }

                $caster = $this->resolveCasterClassByType($definition['cast'] ?? 'string');
            } else {
                $caster = $this->resolveCasterClass($key);
            }

            $this->attributes = array_merge(
                $this->attributes,
                $caster instanceof CastsInboundAttributes
                    ? [$key => $value]
                    : $this->normalizeCastClassResponse($key, $caster->set($this, $key, $value, $this->attributes))
            );
        }
    }
}