<?php

namespace Aindong\Pluggables\Handlers;

use Aindong\Pluggables\Pluggables;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PluggableMakeModelHandler
{
    protected $pluggable;

    protected $finder;

    protected $console;

    protected $moduleName;

    protected $className;

    public function __construct(Pluggables $pluggable, Filesystem $finder)
    {
        $this->pluggable = $pluggable;
        $this->finder = $finder;
    }

    public function fire(Command $console, $slug, $class)
    {
        $this->console = $console;
        $this->moduleName = Str::studly($slug);
        $this->className = studly_case($class);
        if ($this->pluggable->exists($this->moduleName)) {
            $this->makeFile();

            return $this->console->info("Created Module Model: [$this->moduleName] ".$this->getFilename());
        }

        return $this->console->info("Module [$this->moduleName] does not exist.");
    }

    protected function makeFile()
    {
        return $this->finder->put($this->getDestinationFile(), $this->getStubContent());
    }

    protected function getDestinationFile()
    {
        return $this->getPath().$this->formatContent($this->getFilename());
    }

    protected function getPath()
    {
        $path = $this->pluggable->getPluggablePath($this->moduleName);

        return $path.'Models';
    }

    protected function getFilename()
    {
        return $this->className.'.php';
    }

    protected function getStubContent()
    {
        return $this->formatContent($this->finder->get(__DIR__.'/../Console/stubs/model.stub'));
    }

    protected function formatContent($content)
    {
        return str_replace(
            ['{{className}}', '{{moduleName}}', '{{namespace}}'],
            [$this->className, $this->moduleName, $this->pluggable->getNamespace()],
            $content
        );
    }
}
