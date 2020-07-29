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

class TranslateModelMakeCommand extends GeneratorCommand
{
    protected $hidden = true;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:INTranslate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new model translates';

    protected $type = 'ModelTranslate';

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
        if ($this->option('localization')) {
            $this->createLocalizable();
        }
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

    protected function createLocalizable()
    {
        $name = Str::studly(class_basename($this->argument('name')));
        if (!class_exists($name)) {
            $this->call('make:INModel', [
                'name' => "{$name}",
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
        return __DIR__ . '/stubs/translate-model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
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

            ['localization', 'l', InputOption::VALUE_NONE, 'Create a new localization model for translation'],

            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for localization and translate models'],
        ];
    }
}
