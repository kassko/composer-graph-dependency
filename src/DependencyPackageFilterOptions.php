<?php

namespace Kassko\Composer\GraphDependency;

class DependencyPackageFilterOptions
{
    private $dev = false;

    public function makeDev($dev = true)
    {
        $this->dev = $dev;

        return $this;
    }

    public function isDev()
    {
        return $this->dev;
    }
}
