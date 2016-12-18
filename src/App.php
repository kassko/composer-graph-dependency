<?php

namespace Kassko\Composer\GraphDependency;

use Kassko\Composer\GraphDependency\Command;
use Symfony\Component\Application as BaseApp;

class App extends BaseApp
{
    public function __construct()
    {
        parent::__construct('graph-composer', '@git_tag@');

        $this->add(new Command\Export());
        $this->add(new Command\MultiExport());
    }
}
