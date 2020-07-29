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
        parent::__construct($attributes);
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
    public function getTranslateObject($locale)
    {
        $this->refresh('translates');
        return $this->translates->where('locale', $locale)->first()??$this->translates()->save(
                new $this->translates_model_name([
                    'locale' => $locale
                ])
            );
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
        // If the attribute is localizable, we retrieve its translation
        // for the current locale
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

        return parent::__call($method, $arguments);
    }

}
