<?php
namespace Aindong\Pluggables;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

class PluggablesServiceProvider extends ServiceProvider
{
    /**
     * @var bool $defer Indicates if loading of the provider is deferred.
     */
    protected $defer = false;

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {

        // Ensure that this should publish the configuration file
        $this->publishes([
            __DIR__.'/../../config/pluggables.php' => config_path('pluggables.php'),
        ]);
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        // Merge our config file with the application's copy of config file, to allow user to
        // just override the configuration that they wanted to make and keep the defaults that are
        // not touched
        $this->mergeConfigFrom(
            __DIR__.'/../../config/pluggables.php', 'pluggables'
        );

        // Register Services
        $this->registerServices();

        // Register Repository
        $this->registerRepository();

        // Register Migrator
        // Once the migrator instance is set, go ahead and register all migration related commands that are
        // used by the artisan commands for easy access
        $this->registerMigrator();

        // Register Console Commands
        $this->registerConsoleCommands();
    }

    /**
     * Get the services provided by the provider
     *
     * @return array
     */
    public function provides()
    {
        return ['pluggables'];
    }

    /**
     * Register services of the package
     *
     * @return void
     */
    private function registerServices()
    {
        $this->app->bindShared('pluggables', function($app) {
            return new Pluggables($app['config'], $app['files']);
        });

        $this->app->booting(function($app) {
            $app['pluggables']->register();
        });
    }

    /**
     * Register the repository service
     */
    private  function registerRepository()
    {
        $this->app->singleton('migration.repository', function($app) {
            $table = $app['config']['database.migrations'];

            return new DatabaseMigrationRepository($app['db'], $table);
        });


    }

    /**
     * Register the migrator
     *
     * @return void
     */
    private function registerMigrator()
    {
        $this->app->singleton('migrator', function($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }

    /**
     * Register the package console commands
     *
     * @return void
     */
    private function registerConsoleCommands()
    {
        $this->registerMakeCommand();
        $this->registerEnableCommand();
        $this->registerDisableCommand();
        $this->registerListCommand();
        $this->registerMigrateCommand();

        $this->commands([
            'pluggables.make',
            'pluggables.enable',
            'pluggables.disable',
            'pluggables.list',
            'pluggables.migrate'
        ]);
    }

    // TODO: REGISTER CONSOLE COMMANDS

    /**
     * Register the "pluggables:make" console command.
     *
     * @return Console\PluggableMakeCommand
     */
    protected function registerMakeCommand()
    {
        $this->app->bindShared('pluggables.make', function($app) {
            $handler = new Handlers\PluggableMakeHandler($app['pluggables'], $app['files']);
            return new Console\PluggableMakeCommand($handler);
        });
    }

    /**
     * Register the "pluggables:enable" console command.
     *
     * @return Console\PluggableDisableCommand
     */
    protected function registerEnableCommand()
    {
        $this->app->bindShared('pluggables.enable', function() {
            return new Console\PluggableEnableCommand;
        });
    }

    /**
     * Register the "pluggables:disable" console command.
     *
     * @return Console\PluggableDisableCommand
     */
    protected function registerDisableCommand()
    {
        $this->app->bindShared('pluggables.disable', function() {
            return new Console\PluggableDisableCommand;
        });
    }

    /**
     * Register the "pluggables:list" console command.
     *
     * @return Console\PluggableListCommand
     */
    protected function registerListCommand()
    {
        $this->app->bindShared('pluggables.list', function($app) {
            return new Console\PluggableListCommand($app['pluggables']);
        });
    }

    /**
     * Register the "pluggables:make:migrate" console command.
     *
     * @return Console\PluggableMakeMigrationCommand
     */
    protected function registerMigrateCommand()
    {
        $this->app->bindShared('pluggables.migrate', function($app) {
            return new Console\PluggableMakeMigrationCommand($app['migrator'], $app['pluggables']);
        });
    }


}
