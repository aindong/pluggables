<?php

use Mockery as m;
use Aindong\Pluggables\Pluggables;
use Illuminate\Database\Eloquent\Collection;

class PluggablesTest extends PHPUnit_Framework_TestCase
{
    protected $config;
    protected $files;
    protected $module;
    public function setUp()
    {
        parent::setUp();
        $this->config  = m::mock('Illuminate\Config\Repository');
        $this->files   = m::mock('Illuminate\Filesystem\Filesystem');
        $this->module  = new Modules($this->config, $this->files);
    }
    public function tearDown()
    {
        m::close();
    }
    public function testHasCorrectInstance()
    {
        $this->assertInstanceOf('Caffeinated\Modules\Modules', $this->module);
    }
}