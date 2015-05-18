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
        $this->registerMakeMigrationCommand();
        $this->registerMakeRequestCommand();
        $this->registerMigrateCommand();
        $this->registerMigrateRefreshCommand();
        $this->registerMigrateResetCommand();
        $this->registerMigrateRollbackCommand();
        $this->registerSeedCommand();
        $this->registerListCommand();
        $this->commands([
            'pluggables.make',
            'pluggables.enable',
            'pluggables.disable',
            'pluggables.makeMigration',
            'pluggables.makeRequest',
            'pluggables.migrate',
            'pluggables.migrateRefresh',
            'pluggables.migrateReset',
            'pluggables.migrateRollback',
            'pluggables.seed',
            'pluggables.list'
        ]);
    }

    // TODO: REGISTER CONSOLE COMMANDS
//    /**
//     * Register the "module:enable" console command.
//     *
//     * @return Console\ModuleEnableCommand
//     */
//    protected function registerEnableCommand()
//    {
//        $this->app->bindShared('modules.enable', function() {
//            return new Console\ModuleEnableCommand;
//        });
//    }
//    /**
//     * Register the "module:disable" console command.
//     *
//     * @return Console\ModuleDisableCommand
//     */
//    protected function registerDisableCommand()
//    {
//        $this->app->bindShared('modules.disable', function() {
//            return new Console\ModuleDisableCommand;
//        });
//    }
//    /**
//     * Register the "module:make" console command.
//     *
//     * @return Console\ModuleMakeCommand
//     */
//    protected function registerMakeCommand()
//    {
//        $this->app->bindShared('modules.make', function($app) {
//            $handler = new Handlers\ModuleMakeHandler($app['modules'], $app['files']);
//            return new Console\ModuleMakeCommand($handler);
//        });
//    }
//    /**
//     * Register the "module:make:migration" console command.
//     *
//     * @return Console\ModuleMakeMigrationCommand
//     */
//    protected function registerMakeMigrationCommand()
//    {
//        $this->app->bindShared('modules.makeMigration', function($app) {
//            $handler = new Handlers\ModuleMakeMigrationHandler($app['modules'], $app['files']);
//            return new Console\ModuleMakeMigrationCommand($handler);
//        });
//    }
//    /**
//     * Register the "module:make:request" console command.
//     *
//     * @return Console\ModuleMakeRequestCommand
//     */
//    protected function registerMakeRequestCommand()
//    {
//        $this->app->bindShared('modules.makeRequest', function($app) {
//            $handler = new Handlers\ModuleMakeRequestHandler($app['modules'], $app['files']);
//            return new Console\ModuleMakeRequestCommand($handler);
//        });
//    }
//    /**
//     * Register the "module:migrate" console command.
//     *
//     * @return Console\ModuleMigrateCommand
//     */
//    protected function registerMigrateCommand()
//    {
//        $this->app->bindShared('modules.migrate', function($app) {
//            return new Console\ModuleMigrateCommand($app['migrator'], $app['modules']);
//        });
//    }
//    /**
//     * Register the "module:migrate:refresh" console command.
//     *
//     * @return Console\ModuleMigrateRefreshCommand
//     */
//    protected function registerMigrateRefreshCommand()
//    {
//        $this->app->bindShared('modules.migrateRefresh', function() {
//            return new Console\ModuleMigrateRefreshCommand;
//        });
//    }
//    /**
//     * Register the "module:migrate:reset" console command.
//     *
//     * @return Console\ModuleMigrateResetCommand
//     */
//    protected function registerMigrateResetCommand()
//    {
//        $this->app->bindShared('modules.migrateReset', function($app) {
//            return new Console\ModuleMigrateResetCommand($app['modules'], $app['files'], $app['migrator']);
//        });
//    }
//    /**
//     * Register the "module:migrate:rollback" console command.
//     *
//     * @return Console\ModuleMigrateRollbackCommand
//     */
//    protected function registerMigrateRollbackCommand()
//    {
//        $this->app->bindShared('modules.migrateRollback', function($app) {
//            return new Console\ModuleMigrateRollbackCommand($app['modules']);
//        });
//    }
//    /**
//     * Register the "module:seed" console command.
//     *
//     * @return Console\ModuleSeedCommand
//     */
//    protected function registerSeedCommand()
//    {
//        $this->app->bindShared('modules.seed', function($app) {
//            return new Console\ModuleSeedCommand($app['modules']);
//        });
//    }
//    /**
//     * Register the "module:list" console command.
//     *
//     * @return Console\ModuleListCommand
//     */
//    protected function registerListCommand()
//    {
//        $this->app->bindShared('modules.list', function($app) {
//            return new Console\ModuleListCommand($app['modules']);
//        });
//    }

}