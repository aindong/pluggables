<?php
namespace Aindong\Pluggables\Handlers;

use Aindong\Pluggables\Pluggables;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PluggableMakeHandler
{
    /**
     * @var Console
     */
    protected $console;

    /**
     * @var array $folders Module folders to be created.
     */
    protected $folders = [
        'Console/',
        'Database/',
        'Database/Migrations/',
        'Database/Seeds/',
        'Http/',
        'Http/Controllers/',
        'Http/Middleware/',
        'Http/Requests/',
        'Providers/',
        'Resources/',
        'Resources/Lang/',
        'Resources/Views/',
        'Models/',
        'Interfaces/',
        'Repositories/'
    ];

    /**
     * @var array $files Module files to be created.
     */
    protected $files = [
        'Database/Seeds/{{name}}DatabaseSeeder.php',
        'Http/routes.php',
        'Providers/{{name}}ServiceProvider.php',
        'Providers/RouteServiceProvider.php',
        'pluggable.json'
    ];

    /**
     * @var array $stubs Module stubs used to populate defined files.
     */
    protected $stubs = [
        'seeder.stub',
        'routes.stub',
        'moduleserviceprovider.stub',
        'routeserviceprovider.stub',
        'pluggable.stub'
    ];

    /**
     * @var Pluggables
     */
    protected $pluggable;

    /**
     * @var Filesystem
     */
    protected $finder;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor method.
     *
     * @param Pluggables $pluggable
     * @param Filesystem $finder
     */
    public function __construct(Pluggables $pluggable, Filesystem $finder)
    {
        $this->pluggable = $pluggable;
        $this->finder = $finder;
    }

    /**
     * Fire off the handler.
     *
     * @param  \Aindong\Pluggables\Console\PluggableMakeCommand $console
     * @param  string                                         $slug
     * @return bool
     */
    public function fire(Command $console, $slug)
    {
        $this->console = $console;
        $this->slug    = strtolower($slug);
        $this->name    = Str::studly($slug);

        if ($this->pluggable->exists($this->slug)) {
            $console->comment('Pluggable [{$this->name}] already exists.');

            return false;
        }

        $this->generate($console);
    }

    /**
     * Generate pluggable folders and files.
     *
     * @param  \Aindong\Pluggables\Console\PluggableMakeCommand $console
     * @return boolean
     */
    public function generate(Command $console)
    {
        $this->generateFolders();

        $this->generateGitkeep();

        $this->generateFiles();

        $console->info("Pluggable [{$this->name}] has been created successfully.");

        return true;
    }

    /**
     * Generate defined pluggable folders.
     *
     * @return void
     */
    protected function generateFolders()
    {
        if (! $this->finder->isDirectory($this->pluggable->getPath())) {
            $this->finder->makeDirectory($this->pluggable->getPath());
        }

        $this->finder->makeDirectory($this->getPluggablePath($this->slug, true));

        foreach ($this->folders as $folder) {
            $this->finder->makeDirectory($this->getPluggablePath($this->slug).$folder);
        }
    }

    /**
     * Generate defined pluggable files.
     *
     * @return void
     */
    protected function generateFiles()
    {
        foreach ($this->files as $key => $file) {
            $file = $this->formatContent($file);

            $this->makeFile($key, $file);
        }
    }

    /**
     * Generate .gitkeep files within generated folders.
     *
     * @return null
     */
    protected function generateGitkeep()
    {
        $pluggablePath = $this->getPluggablePath($this->slug);

        foreach ($this->folders as $folder) {
            $gitkeep    = $pluggablePath.$folder.'/.gitkeep';

            $this->finder->put($gitkeep, '');
        }
    }

    /**
     * Create pluggable file.
     *
     * @param  int     $key
     * @param  string  $file
     * @return int
     */
    protected function makeFile($key, $file)
    {
        return $this->finder->put($this->getDestinationFile($file), $this->getStubContent($key));
    }

    /**
     * Get the path to the pluggable.
     *
     * @param  string $slug
     * @return string
     */
    protected function getPluggablePath($slug = null, $allowNotExists = false)
    {
        if ($slug) {
            return $this->pluggable->getPluggablePath($slug, $allowNotExists);
        }

        return $this->pluggable->getPath();
    }

    /**
     * Get destination file.
     *
     * @param  string $file
     * @return string
     */
    protected function getDestinationFile($file)
    {
        return $this->getPluggablePath($this->slug).$this->formatContent($file);
    }

    /**
     * Get stub content by key.
     *
     * @param  int $key
     * @return string
     */
    protected function getStubContent($key)
    {
        return $this->formatContent($this->finder->get(__DIR__.'/../Console/stubs/'.$this->stubs[$key]));
    }

    /**
     * Replace placeholder text with correct values.
     *
     * @return string
     */
    protected function formatContent($content)
    {
        return str_replace(
            ['{{slug}}', '{{name}}', '{{namespace}}'],
            [$this->slug, $this->name, $this->pluggable->getNamespace()],
            $content
        );
    }
}