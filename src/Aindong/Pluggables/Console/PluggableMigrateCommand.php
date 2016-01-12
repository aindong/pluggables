<?php

namespace Aindong\Pluggables\Console;

use Aindong\Pluggables\Pluggables;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PluggableMigrateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string The console command name.
     */
    protected $name = 'pluggables:migrate';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run the database migrations for a specific or all pluggables';

    protected $pluggable;
    /**
     * @var \Illuminate\Database\Migrations\Migrator The migrator instance.
     */
    protected $migrator;

    public function __construct(Migrator $migrator, Pluggables $pluggable)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->pluggable = $pluggable;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        if (!$this->confirmToProceed()) {
            return;
        }
        $this->prepareDatabase();
        $pluggable = $this->pluggable->getProperties($this->argument('pluggable'));

        if (!empty($pluggable)) {
            if ($this->pluggable->isEnabled($pluggable['slug'])) {
                return $this->migrate($pluggable['slug']);
            } elseif ($this->option('force')) {
                return $this->migrate($pluggable['slug']);
            }
        } else {
            if ($this->option('force')) {
                $pluggables = $this->pluggable->all();
            } else {
                $pluggables = $this->pluggable->getByEnabled();
            }
            foreach ($pluggables as $pluggable) {
                $this->migrate($pluggable['slug']);
            }
        }
    }

    /**
     * Run migrations for the specified module.
     *
     * @param string $slug
     *
     * @return mixed
     */
    protected function migrate($slug)
    {
        $moduleName = Str::studly($slug);
        if ($this->pluggable->exists($moduleName)) {
            $pretend = $this->option('pretend');
            $path = $this->getMigrationPath($slug);
            $this->migrator->run($path, $pretend);

            foreach ($this->migrator->getNotes() as $note) {
                if (!$this->option('quiet')) {
                    $this->output->writeln($note);
                }
            }

            if ($this->option('seed')) {
                $this->call('pluggable:seed', ['pluggable' => $slug, '--force' => true]);
            }
        } else {
            return $this->error("Pluggable [$moduleName] does not exist.");
        }
    }

    /**
     * Get migration directory path.
     *
     * @param string $slug
     *
     * @return string
     */
    protected function getMigrationPath($slug)
    {
        $path = $this->pluggable->getPluggablePath($slug);

        return $path.'Database/Migrations/';
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->option('database'));
        if (!$this->migrator->repositoryExists()) {
            $options = ['--database' => $this->option('database')];
            $this->call('migrate:install', $options);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [['pluggable', InputArgument::OPTIONAL, 'Pluggable slug.']];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run while in production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
        ];
    }
}
