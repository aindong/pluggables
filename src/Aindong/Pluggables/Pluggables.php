<?php
namespace Aindong\Pluggables;

use App;
use Countable;
use Aindong\Pluggables\Exceptions\FileNotFoundException;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Pluggables implements Countable
{

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string $path Path to the defined pluggables directory
     */
    protected $path;

    /**
     * Constructor for dependency injections
     *
     * @param Repository $config
     * @param Filesystem $files
     */
    public function __construct(Repository $config, Filesystem $files)
    {
        $this->config = $config;
        $this->files  = $files;
    }

    /**
     * Register the module service provider from all pluggables
     */
    public function register()
    {
        foreach ($this->enabled() as $pluggable) {
            $this->registerServiceProvider($pluggable);
        }
    }

    /**
     * Register the module service provider
     *
     * @param $pluggable
     * @throws FileNotFoundException
     */
    protected function registerServiceProvider($pluggable)
    {
        $pluggable      = Str::studly($pluggable['slug']);
        $file           = $this->getPath().'/{$pluggable}/Providers/{$pluggable}ServiceProvider.php';
        $namespace      = $this->getNamespace().$pluggable.'\\Providers\\{$pluggable}ServiceProvider';

        if ( ! $this->files->exists($file)) {
            $message = 'Pluggable [{$pluggable}] must have a "{$pluggable}/Providers/{$pluggable}ServiceProvider.php" file';

            throw new FileNotFoundException($message);
        }

        App::register($namespace);
    }

    /**
     * Get all the pluggables
     *
     * @return Collection
     */
    public function all()
    {
        $pluggables     = [];
        $allPlugs       = $this->getAllBaseNames();

        foreach ($allPlugs as $plug) {
            $pluggables[] = $this->getJsonContents($plug);
        }

        return new Collection($this->sortByOrder($pluggables));
    }

    /**
     * Get all pluggable basenames
     *
     * @return array
     */
    protected function getAllBaseNames()
    {
        $pluggables = [];

        $path    = $this->getPath();

        if ( ! is_dir($path)) {
            return $pluggables;
        }

        $folders = $this->files->directories($path);

        foreach ($folders as $plug) {
            $pluggables[] = basename($plug);
        }

        return $pluggables;
    }

    /**
     * Get all pluggable slugs.
     *
     * @return array
     */
    protected function getAllSlugs()
    {
        $pluggables = $this->all();
        $slugs   = array();
        foreach ($pluggables as $plug)
        {
            $slugs[] = $plug['slug'];
        }

        return $slugs;
    }

    /**
     * Check if given pluggable path exists.
     *
     * @param  string  $folder
     * @return bool
     */
    protected function pathExists($folder)
    {
        $folder = Str::studly($folder);

        return in_array($folder, $this->getAllBaseNames());
    }

    /**
     * Check if the given pluggable exists.
     *
     * @param  string  $slug
     * @return bool
     */
    public function exists($slug)
    {
        $slug = strtolower($slug);

        return in_array($slug, $this->getAllSlugs());
    }

    /**
     * Returns count of all pluggables.
     *
     * @return int
     */
    public function count()
    {
        return count($this->all());
    }

    /**
     * Get pluggables path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path ?: $this->config->get('pluggables.path');
    }

    /**
     * Set pluggables path in "RunTime" mode.
     *
     * @param  string $path
     * @return object $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get pluggales namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->config->get('pluggables.namespace');
    }

    /**
     * Get path for the specified pluggable.
     *
     * @param  string $slug
     * @return string
     */
    public function getPluggablePath($slug, $allowNotExists = false)
    {
        $pluggable = Str::studly($slug);

        if ( ! $this->pathExists($pluggable) && $allowNotExists === false) {
            return null;
        }

        return $this->getPath()."/{$pluggable}/";
    }

    /**
     * Get a pluggable's properties.
     *
     * @param  string $slug
     * @return mixed
     */
    public function getProperties($slug)
    {
        return $this->getJsonContents($slug);
    }

    /**
     * Get a pluggable property value.
     *
     * @param  string $property
     * @param  mixed  $default
     * @return mixed
     */
    public function getProperty($property, $default = null)
    {
        list($pluggable, $key) = explode('::', $property);

        return array_get($this->getJsonContents($pluggable), $key, $default);
    }

    /**
     * Set a pluggale property value.
     *
     * @param  string $property
     * @param  mixed  $value
     * @return bool
     */
    public function setProperty($property, $value)
    {
        list($pluggable, $key) = explode('::', $property);

        $content = $this->getJsonContents($pluggable);

        if (count($content)) {
            if (isset($content[$key])) {
                unset($content[$key]);
            }

            $content[$key] = $value;
            $this->setJsonContents($pluggable, $content);

            return true;
        }

        return false;
    }


    /**
     * Find a pluggable with enabled status given
     *
     * @param  bool $enabled
     * @return array
     */
    public function getByEnabled($enabled = true)
    {
        $disabledPluggables = array();
        $enabledPluggables  = array();

        $pluggables         = $this->all();

        // Iterate through each pluggable
        foreach ($pluggables as $pluggable) {

            if ($this->isEnabled($pluggable['slug'])) {
                $enabledPluggables[] = $pluggable;
            } else {
                $disabledPluggables[] = $pluggable;
            }

        }

        if ($enabled === true) {
            return $this->sortByOrder($enabledPluggables);
        }

        return $this->sortByOrder($disabledPluggables);
    }

    /**
     * Simple alias for getByEnabled(true).
     *
     * @return array
     */
    public function enabled()
    {
        return $this->getByEnabled(true);
    }

    /**
     * Simple alias for getByEnabled(false).
     *
     * @return array
     */
    public function disabled()
    {
        return $this->getByEnabled(false);
    }

    /**
     * Check if specified pluggable is enabled.
     *
     * @param  string $slug
     * @return bool
     */
    public function isEnabled($slug)
    {
        return $this->getProperty("{$slug}::enabled") === true;
    }

    /**
     * Check if specified pluggable is disabled.
     *
     * @param  string $slug
     * @return bool
     */
    public function isDisabled($slug)
    {
        return $this->getProperty("{$slug}::enabled") === false;
    }

    /**
     * Enables the specified pluggable.
     *
     * @param  string $slug
     * @return bool
     */
    public function enable($slug)
    {
        return $this->setProperty("{$slug}::enabled", true);
    }

    /**
     * Disables the specified pluggable.
     *
     * @param  string $slug
     * @return bool
     */
    public function disable($slug)
    {
        return $this->setProperty("{$slug}::enabled", false);
    }

    /**
     * Get pluggable JSON content as an array.
     *
     * @param  string $pluggable
     * @return array|mixed
     * @throws FileNotFoundException
     */
    protected function getJsonContents($pluggable)
    {
        $pluggable = Str::studly($pluggable);
        $default = [];

        if ( ! $this->pathExists($pluggable)) {
            return $default;
        }

        $path = $this->getJsonPath($pluggable);

        if ($this->files->exists($path)) {
            $contents = $this->files->get($path);

            return json_decode($contents, true);
        } else {
            $message = "Pluggable [{$pluggable}] must have a valid pluggable.json file.";

            throw new FileNotFoundException($message);
        }
    }

    /**
     * Set pluggable JSON content property value.
     *
     * @param  string $pluggable
     * @param  array  $content
     * @return int
     */
    public function setJsonContents($pluggable, array $content)
    {
        $pluggable = strtolower($pluggable);
        $content = json_encode($content, JSON_PRETTY_PRINT);

        return $this->files->put($this->getJsonPath($pluggable), $content);
    }

    /**
     * Get path of pluggable JSON file.
     *
     * @param  string $module
     * @return string
     */
    protected function getJsonPath($module)
    {
        return $this->getPluggablePath($module).'/pluggable.json';
    }

    /**
     * Sort pluggables by order.
     *
     * @param  array  $pluggables
     * @return array
     */
    public function sortByOrder($pluggables)
    {
        $orderedPluggables = array();

        foreach ($pluggables as $pluggable) {
            if (! isset($pluggable['order'])) {
                $pluggable['order'] = 9001;  // It's over 9000!
            }
            $orderedPluggables[] = $pluggable;
        }

        if (count($orderedPluggables) > 0) {
            $orderedPluggables = $this->arrayOrderBy($orderedPluggables, 'order', SORT_ASC, 'slug', SORT_ASC);
        }

        return $orderedPluggables;
    }

    /**
     * Helper method to order multiple values easily.
     *
     * @return array
     */
    protected function arrayOrderBy()
    {
        $arguments = func_get_args();
        $data      = array_shift($arguments);
        foreach ($arguments as $argument => $field) {
            if (is_string($field)) {
                $temp = array();
                foreach ($data as $key => $row) {
                    $temp[$key] = $row[$field];
                }
                $arguments[$argument] = $temp;
            }
        }
        $arguments[] =& $data;
        call_user_func_array('array_multisort', $arguments);
        return array_pop($arguments);
    }
}