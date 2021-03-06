<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   GNU General Public License v3.0
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

    /**
     * Get the migration stub file.
     *
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        $stub = $this->stubPath() . (
            is_null($table)
                ? '/blank.stub'
                : (
            $create
                ? '/create.stub'
                : '/update.stub'
            )
            );

        return $this->files->get($stub);
    }
}
