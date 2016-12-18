<?php

namespace Kassko\Composer\GraphDependency;

class DependencyPackageFilterOptions
{
    private $dev;

    public function makeDev()
    {
        $this->dev = $dev;
    }

    public function isDev()
    {
        return $this->dev();
    }
}
