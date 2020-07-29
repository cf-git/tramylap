<?php
/**
 * @package   Laravel localization package
 * @author    Sergei Shubin <is.captain.fail@gmail.com>
 * @copyright 2018
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   0.1.7
 */

namespace CFGit\Tramylap;

use CFGit\Tramylap\Commands\LocalizableMigrationMakeCommand;
use CFGit\Tramylap\Commands\LocalizableModelMakeCommand;
use CFGit\Tramylap\Commands\Migration\Creator;
use CFGit\Tramylap\Commands\TranslateModelMakeCommand;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class CFServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            ( __DIR__.'/resources/config/tramylap.php') => config_path('tramylap.php'),
        ], 'config');
        $this->publishes([
            ( __DIR__.'/resources/assets') => public_path('vendor/cf-git/tramylap'),
        ], 'assets');
        $this->registerCommands();
    }

    public function registerCommands()
    {
        $this->app->singleton("command.migrate.make.tramylap", function (Application $app) {
            return new LocalizableMigrationMakeCommand(new Creator($app['files'], $app->basePath('stubs')), $app['composer']);
        });
        $this->app->singleton("command.model.make.tramylap", function (Application $app) {
            return new LocalizableModelMakeCommand($app['files']);
        });
        $this->app->singleton("command.model.make.translate", function (Application $app) {
            return new TranslateModelMakeCommand($app['files']);
        });
        $this->commands([
            "command.migrate.make.tramylap",
            "command.model.make.tramylap",
            "command.model.make.translate",
        ]);
    }

    /**
     * Boot the instance, add macros for datatable engines.
     *
     * @return void
     */
    public function boot()
    {
    }
}
