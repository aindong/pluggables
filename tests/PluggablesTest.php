<?php

use Mockery as m;
use Aindong\Pluggables\Pluggables;
use Illuminate\Database\Eloquent\Collection;

class PluggablesTest extends PHPUnit_Framework_TestCase
{
    protected $config;
    protected $files;
    protected $pluggable;

    public function setUp()
    {
        parent::setUp();
        $this->config  = m::mock('Illuminate\Config\Repository');
        $this->files   = m::mock('Illuminate\Filesystem\Filesystem');
        $this->pluggable  = new Pluggables($this->config, $this->files);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testHasCorrectInstance()
    {
        $this->assertInstanceOf('Aindong\Pluggables\Pluggables', $this->pluggable);
    }
}