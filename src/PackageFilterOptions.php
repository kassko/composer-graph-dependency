<?php

namespace Kassko\Composer\GraphDependency;

class PackageFilterOptions
{
    private $root = true;

    public function makeRoot($root = true)
    {
        $this->root = $root;

        return $this;
    }

    public function isRoot()
    {
        return $this->root;
    }
}
