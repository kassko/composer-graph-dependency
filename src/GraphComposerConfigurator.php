<?php

namespace Kassko\Composer\GraphDependency;

use Kassko\Composer\GraphDependency\DependencyAnalyzer;
use Kassko\Composer\GraphDependency\PackageFilter;

class GraphComposerConfigurator
{
    private $packageFilter;
    private $dir;
    private $dependencyAnalyzer;

    public function __construct($dir, PackageFilter $packageFilter, DependencyAnalyzer $dependencyAnalyzer)
    {
        $this->dir = $dir;
        $this->packageFilter = $packageFilter;
        $this->dependencyAnalyzer = $dependencyAnalyzer;
    }

    public function configure(GraphComposer $graphComposer)
    {
        $graphComposer->setDependencyGraph($this->dependencyAnalyzer->analyze($this->dir, $this->packageFilter));
    }
}
