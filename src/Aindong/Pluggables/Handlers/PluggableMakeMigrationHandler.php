<?php
namespace Aindong\Pluggables\Handlers;

use Aindong\Pluggables\Pluggables;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PluggableMakeMigrationHandler
{
    /**
     * @var Pluggables
     */
    protected $pluggable;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $finder;

    /**
     * @var \Illuminate\Console\Command
     */
    protected $console;

    /**
     * @var string $moduleName The name of the module
     */
    protected $pluggableName;

    /**
     * @var string $table The name of the table
     */
    protected $table;

    /**
     * @var string $migrationName The name of the migration
     */
    protected $migrationName;

    /**
     * @var string $className The name of the migration class
     */
    protected $className;

    /**
     * Constructor method.
     *
     * @param Pluggables      $module
     * @param \Illuminate\Filesystem\Filesystem $finder
     */
    public function __construct(Pluggables $pluggable, Filesystem $finder)
    {
        $this->pluggable = $pluggable;
        $this->finder = $finder;
    }

    /**
     * Fire off the handler.
     *
     * @param  \Aindong\Pluggables\Console\PluggableMakeMigrationCommand    $console
     * @param  string                                                       $slug
     * @return string
     */
    public function fire(Command $console, $slug, $table)
    {
        $this->console          = $console;
        $this->pluggableName    = Str::studly($slug);
        $this->table            = strtolower($table);
        $this->migrationName    = snake_case($this->table);
        $this->className        = studly_case($this->migrationName);

        if ($this->pluggable->exists($this->pluggableName)) {
            $this->makeFile();

            $this->console->info("Created Pluggable Migration: [$this->pluggableName] ".$this->getFilename());

            return exec('composer dump-autoload');
        }

        return $this->console->info("Pluggable [$this->pluggableName] does not exist.");
    }

    /**
     * Create new migration file.
     *
     * @return int
     */
    protected function makeFile()
    {
        return $this->finder->put($this->getDestinationFile(), $this->getStubContent());
    }

    /**
     * Get file destination.
     *
     * @return string
     */
    protected function getDestinationFile()
    {
        return $this->getPath().$this->formatContent($this->getFilename());
    }

    /**
     * Get module migration path.
     *
     * @return string
     */
    protected function getPath()
    {
        $path = $this->pluggable->getPluggablePath($this->pluggableName);

        return $path.'Database/Migrations/';
    }

    /**
     * Get migration filename.
     *
     * @return string
     */
    protected function getFilename()
    {
        return date("Y_m_d_His").'_'.$this->migrationName.'.php';
    }

    /**
     * Get stub content.
     *
     * @return string
     */
    protected function getStubContent()
    {
        return $this->formatContent($this->finder->get(__DIR__.'/../Console/stubs/migration.stub'));
    }

    /**
     * Replace placeholder text with correct values.
     *
     * @param  string $content
     * @return string
     */
    protected function formatContent($content)
    {
        return str_replace(
            ['{{migrationName}}', '{{table}}'],
            [$this->className, $this->table],
            $content
        );
    }
}