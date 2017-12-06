<?php

namespace Kassko\Composer\GraphDependency;

class Utils
{
	public static function getOptionAsArray($option)
    {
        if (empty($option)) {
            return [];
        }

        return explode(' ', $option);
    }

    public static function extractPackageNameParts($packageFullName)
    {
        if (false === strpos($packageFullName, '/')) {
            $packageFullName = $packageFullName . '/' . $packageFullName;
        }

        return explode('/', $packageFullName, 2);
    }
}
