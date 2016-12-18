<?php

namespace Kassko\Composer\GraphDependency;

class PackageFilterOptions
{
    private $root;
    private $dev;

    public function makeRoot()
    {
        $this->root = $root;
    }

    public function isRoot()
    {
        return $this->root();
    }
}
