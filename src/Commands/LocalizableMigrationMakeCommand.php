<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   0.1.7
 */
namespace CFGit\Tramylap\Commands;


use CFGit\Tramylap\Commands\Migration\Creator;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class LocalizableMigrationMakeCommand extends MigrateMakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:INMigration {name : The name of the migration}
        {--m|model= : Create base model to localization}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    public function __construct(Creator $creator, Composer $composer)
    {
        parent::__construct($creator, $composer);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('model')) {
            $this->createLocalizable();
        }
        parent::handle();
    }

    protected function createLocalizable()
    {
        $name = Str::studly(Str::singular(TableGuesser::guess($this->argument('name'))[0]));
        if (!class_exists($name)) {
            $this->call('make:INModel', [
                'name' => "{$name}",
            ]);
        }
    }
}
