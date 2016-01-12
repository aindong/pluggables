<?php

namespace Aindong\Pluggables\Traits;

trait MigrationTrait
{
    /**
     * Require (once) all migration files for the supplied module.
     *
     * @param string $pluggable
     *
     * @return void
     */
    protected function requireMigrations($pluggable)
    {
        $path = $this->getMigrationPath($pluggable);

        $migrations = $this->laravel['files']->glob($path.'*_*.php');

        foreach ($migrations as $migration) {
            $this->laravel['files']->requireOnce($migration);
        }
    }

    /**
     * Get migration directory path.
     *
     * @param string $pluggable
     *
     * @return string
     */
    protected function getMigrationPath($pluggable)
    {
        $path = $this->laravel['pluggables']->getPluggablePath($pluggable);

        return $path.'Database/Migrations/';
    }
}
