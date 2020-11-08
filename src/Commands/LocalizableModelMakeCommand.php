<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   GNU General Public License v3.0
 * @version   0.1.7
 */

namespace CFGit\Tramylap\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class LocalizableModelMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:INModel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new model localization';

    protected $type = 'LocalizableModel';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {

        if (parent::handle() === false && !$this->option('force')) {
            return;
        }
        $this->createTranslates();

        if ($this->option('migration')) {
            $this->createMigration();
        }
    }


    protected function createMigration()
    {
        $name = 'create_'.Str::plural(Str::snake(class_basename($this->argument('name')))).'_table';
        if (!class_exists($name)) {
            $this->call('make:INMigration', [
                'name' => "{$name}",
            ]);
        }
    }

    protected function createTranslates()
    {
        $name = Str::studly($this->argument('name'));

        if (!class_exists($name . 'Translate')) {
            $this->call('make:INTranslate', [
                'name' => "{$name}Translate"
            ]);
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/localizable-model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $subSpace = is_dir(app_path("Models")) ? "\\Models" : "";
        return $subSpace.$rootNamespace;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [

            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],

//            ['translates', 't', InputOption::VALUE_NONE, 'Create a new translates model for keeping translates'],

            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for localization and translate models'],
        ];
    }
}
