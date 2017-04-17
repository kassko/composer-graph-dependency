<?php

namespace Kassko\Composer\GraphDependency;

class PackageFilterOptions
{
    private $root;

    public function makeRoot()
    {
        $this->root = $root;
    }

    public function isRoot()
    {
        return $this->root();
    }
}
