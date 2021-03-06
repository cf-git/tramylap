<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   GNU General Public License v3.0
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
        $localizableBaseName = substr(class_basename(get_class($this)), 0, -9);
        $subSpace = is_dir(app_path("Models")) ? "\\Models" : "";
        $this->translatableModel = ("App{$subSpace}\\{$localizableBaseName}");
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
