<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   0.1.7
 */

namespace CFGit\Tramylap;

use Illuminate\Database\Eloquent\Model;

abstract class TranslatesModel extends Model
{
    protected $translatableModel = null;
    protected $blank = null;
    protected $fillable = [];
    public function __construct($attributes = [])
    {
        $this->bootIfNotBooted();
        $this->translatableModel = "App\\Models\\".substr(class_basename(get_class($this)), 0, -9);
        $this->blank = $translatableObject = with(new $this->translatableModel);
        $this->fillable = $translatableObject->getLocalizableFields()??[];
        $this->fillable[] = 'locale';
        $this->fillable[] = $translatableObject->getForeignKey();
        parent::__construct($attributes);
    }

    public function translatable()
    {
        return $this->belongsTo($this->translatableModel, $this->blank->getForeignKey());
    }
}
