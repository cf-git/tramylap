<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   0.1.7
 */
namespace CFGit\Tramylap\Commands\Migration;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;

class Creator extends MigrationCreator
{
    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/../stubs';
    }
}
