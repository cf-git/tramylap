<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   GNU General Public License v3.0
 * @version   0.1.7
 */

namespace CFGit\Tramylap;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

abstract class LocalizableModel extends Model
{

    protected $appendLocalizableAccessorsList = true;

    protected $localizableAccessorsList = [];

    protected $appendLocalizableMutatorsList = true;

    protected $localizableMutatorsList = [];

    /**
     * Localized attributes
     *
     * @var array
     */
    protected $localizable = [];

    /**
     * Localized attributes as [property]_[locale]
     *
     * @var array
     */
    protected $localized_property = [];


    /**
     * Whether or not to eager load translates
     *
     * @var boolean
     */
    protected $eagerLoadTranslates = true;

    /**
     * Whether or not to hide translates
     *
     * @var boolean
     */
    protected $hideTranslates = false;

    /**
     * Whether or not to append translatable attributes to array output
     *
     * @var boolean
     */
    protected $appendLocalizedAttributes = true;


    /**
     * Make a new translatable model
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->bootIfNotBooted();
        if ($this->eagerLoadTranslates) {
            $this->with[] = 'translates';
        }

        if ($this->hideTranslates) {
            $this->hidden[] = 'translates';
        }
        // We dynamically append localizable attributes to array output
        if ($this->appendLocalizedAttributes) {
            foreach ($this->localizable as $localizableAttribute) {
                $this->appends[] = $localizableAttribute;
                foreach (array_column(config('tramylap.locales'),'slug') as $locale) {
                    $this->localized_property[] = $localizableAttribute.'_'.$locale;
                    $this->appends[] = $localizableAttribute.'_'.$locale;
                }
            }
        }
        if($this->appendLocalizableAccessorsList) {
            foreach ($this->localizable as $localizableAttribute) {
                $this->localizableAccessorsList[] = 'get' . Str::studly($localizableAttribute) . 'Attribute';
                foreach (array_column(config('tramylap.locales'),'slug') as $locale) {
                    $this->localizableAccessorsList[] = 'get' . Str::studly($localizableAttribute.'_'.$locale) . 'Attribute';
                }
            }
        }
        if($this->appendLocalizableMutatorsList) {
            foreach ($this->localizable as $localizableAttribute) {
                $this->localizableMutatorsList[] = 'set' . Str::studly($localizableAttribute) . 'Attribute';
                foreach (array_column(config('tramylap.locales'),'slug') as $locale) {
                    $this->localizableMutatorsList[] = 'set' . Str::studly($localizableAttribute.'_'.$locale) . 'Attribute';
                }
            }
        }
        parent::__construct($attributes);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return in_array('set'.Str::studly($key).'Attribute', $this->localizableMutatorsList) || parent::hasSetMutator($key);
    }

    /**
     * This model's translates
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translates()
    {
        return $this->hasMany($this->getTranslatesModelName());
    }

    /**
     * @return string
     */
    public function getTranslatesModelName()
    {
        $modelName = class_basename(get_class($this));
        return "\\App\\Models\\{$modelName}Translate";
    }

    /**
     * @param $locale
     * @return false|Model
     */
    public function getTranslateObject($locale, $attributes = [])
    {
        if ($this->exists) {
            if ($this->isDirty()) $this->save();
            $this->refresh('translates');
            if (is_null($tObj = $this->translates->where('locale', $locale)->first())) {
                $tObj = $this->translates()->save(
                    new $this->translates_model_name([
                        'locale' => $locale
                    ])
                );
            }
            if (!empty($attributes)) {
                $tObj->fill($attributes)->save();
            }
            return $tObj;
        } else {
            static::created(function($model) use ($locale, $attributes) {
                if ($model === $this) {
                    $attributes['locale'] = $locale;
                    $this->translates()->save(
                        new $this->translates_model_name($attributes)
                    );
                }
            });
        }
    }

    /**
     * @return string
     */
    public function getTranslatesModelNameAttribute()
    {
        return $this->getTranslatesModelName();
    }

    /**
     * Get localizabled fields
     * @return array
     */
    public function getLocalizableFields()
    {
        return $this->localizable;
    }

    /**
     * Magic method for retrieving a missing attribute
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        foreach ($this->localizable as $localizableAttribute) {
            if (in_array($attribute, $this->localized_property)) {
                $property = explode('_', $attribute);
                $locale = array_pop($property);
                $property = implode('_', $property);
                try {
                    return $this->translates
                        ->where('locale', $locale)
                        ->first()
                        ->{$property};
                } catch (\Throwable $e) {}
            }
            if ($attribute === $localizableAttribute) {
                try {
                    return $this->translates
                            ->where('locale', app()->getLocale())
                            ->first()
                            ->{$localizableAttribute}??$this->translates
                            ->where('locale', config('app.fallback_locale'))
                            ->first()
                            ->{$localizableAttribute};
                } catch (\Throwable $e) {}
                try {
                    return $this->translates
                        ->where('locale', config('app.fallback_locale'))
                        ->first()
                        ->{$localizableAttribute};
                } catch (\Throwable $e) {}
            }
        }

        return parent::__get($attribute);
    }

    /**
     * Magic method for calling a missing instance method
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (in_array($method, $this->localizableAccessorsList)) {
            foreach ($this->localized_property as $localizableAttribute) {
                if ($method === 'get' . Str::studly($localizableAttribute) . 'Attribute') {
                    return $this->{$localizableAttribute};
                }
            }
            foreach ($this->localizable as $localizableAttribute) {
                if ($method === 'get' . Str::studly($localizableAttribute) . 'Attribute') {
                    return $this->{$localizableAttribute};
                }
            }
        }

        if (in_array($method, $this->localizableMutatorsList)) {
            foreach ($this->localizable as $localizableAttribute) {
                // Must be array - value[{locale}]
                if ($method === ('set' . Str::studly($localizableAttribute) . 'Attribute')) {
                    return $this;
                }
            }
            foreach ($this->localized_property as $localizableAttribute) {
                // Single value - value_{locale}
                if ($method === ('set' . Str::studly($localizableAttribute) . 'Attribute')) {
                    list($value) = $arguments;
                    $segments = explode('_', $localizableAttribute);
                    $locale = array_pop($segments);
                    $attribute = implode('_', $segments);
                    $tObj = $this->getTranslateObject($locale, [
                        $attribute => $value
                    ]);
                    return $this;
                }
            }
        }

        return parent::__call($method, $arguments);
    }

}
