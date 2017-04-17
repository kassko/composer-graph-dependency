<?php

namespace Kassko\Composer\GraphDependency;

class PackageFilter
{
    private $filterConfig;

    public function __construct(array $filterConfig)
    {
        $this->filterConfig = $filterConfig;
    }

    public function filter($packageFullName, PackageFilterOptions $packageFilterOptions, array $packageData)
    {
        if ($packageFilterOptions->isRoot()) {
            return false;
        }

        if (count($this->filterConfig['include_tags'])) {
            if (!isset($packageData['extra'])) {
                return true;
            }

            foreach ($this->filterConfig['include_tags'] as $tagConfig) {
                if ($this->valueExistsInPath($tagConfig['path'], $tagConfig['value'], $packageData['extra'])) {
                    return false;
                }
            }
        }

        if (count($this->filterConfig['exclude_tags']) && isset($packageData['extra'])) {
            foreach ($this->filterConfig['exclude_tags'] as $tagConfig) {
                if ($this->valueExistsInPath($tagConfig['path'], $tagConfig['value'], $packageData['extra'])) {
                    return true;
                }
            }
        }

        if ('includes_all' === $this->filterConfig['default_filtering_mode']) {
            if (in_array($packageFullName, $this->filterConfig['exclude_packages']) {
                return true;
            }
            
            list($vendorName, $packageName) = explode('/', $packageFullName, 2);
            
            if (in_array($vendorName, $this->filterConfig['exclude_vendors']) {
                return true;
            }    

            return false;
        } 

        /** elseif ('excludes_all' === $this->filterConfig['default_filtering_mode']) */

        if (in_array($packageFullName, $this->filterConfig['include_packages']) {
            return false;
        }

        list($vendorName, $packageName) = explode('/', $packageFullName, 2);

        if (in_array($vendorName, $this->filterConfig['include_vendors']) {
            return false;
        }

        return true;
    }

    protected function filterDependency($packageFullName, DependencyPackageFilterOptions $packageFilterOptions, array $parentPackageData)
    {
        if ($this->filterConfig['no-root-dev-dep'] && $packageFilterOptions->isDev()) {
            return true;
        }

        if ('includes_all' === $this->filterConfig['default_dependency_filtering_mode']) {
            if (in_array($packageFullName, $this->filterConfig['exclude_dependency_packages']) {
                return true;
            }
            
            list($vendorName, $packageName) = explode('/', $packageFullName, 2);
            
            if (in_array($vendorName, $this->filterConfig['exclude_dependency_vendors']) {
                return true;
            }    

            return false;
        } 

        /** elseif ('excludes_all' === $this->filterConfig['default_dependency_filtering_mode']) */

        if (in_array($packageFullName, $this->filterConfig['include_dependency_packages']) {
            return false;
        }

        list($vendorName, $packageName) = explode('/', $packageFullName, 2);

        if (in_array($vendorName, $this->filterConfig['include_dependency_vendors']) {
            return false;
        }

        return true;
    }

    protected function valueExistsInPath($path, $value, array $config)
    {
        $pathParts = explode('.', $path);

        $nbPartsFound = 0;
        foreach ($pathParts as $pathPart) {
            if (isset($config[$pathPart])) {
                $config = $config[$pathPart];
                $nbPartsFound++;
            } else {
                break;
            }
        }

        return count($pathParts) === $nbPartsFound && $config === $value;
    }
}