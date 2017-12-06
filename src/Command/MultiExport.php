<?php

namespace Kassko\Composer\GraphDependency\Command;

use Kassko\Composer\GraphDependency\DependencyAnalyzer;
use Kassko\Composer\GraphDependency\GraphComposer;
use Kassko\Composer\GraphDependency\GraphComposerConfigurator;
use Kassko\Composer\GraphDependency\PackageFilter;
use Kassko\Composer\GraphDependency\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MultiExport extends Command
{
    protected function configure()
    {
        $this->setName('export')
             ->setDescription('Export dependency graph images for given project directory and given packages or vendors')
             ->addArgument('root-dir', InputArgument::OPTIONAL, 'Path to project directory to scan', realpath('.'))
             ->addArgument('output', InputArgument::OPTIONAL, 'Directory to output images files.', './composer-dependency')

             // add output format option. default value MUST NOT be given, because default is to overwrite with output extension
             ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Image format (svg, png, jpeg)', 'svg')

             ->addOption('packages', null, InputOption::VALUE_REQUIRED, 'Packages to display in the graph: "vendor-name/package-name"', '')
             ->addOption('no-packages', null, InputOption::VALUE_REQUIRED, 'Packages not to display in the graph: "vendor-name/package-name"', '')

             ->addOption('vendors', null, InputOption::VALUE_REQUIRED, 'Vendor packages to display in the graph: "vendor-name"', '')
             ->addOption('no-vendors', null, InputOption::VALUE_REQUIRED, 'Vendor packages not to display in the graph: "vendor-name"', '')

             ->addOption('dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages to display in the graph: "vendor-name/package-name"', '')
             ->addOption('no-dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages not to display in the graph: "vendor-name/package-name"', '')

             ->addOption('dep-vendors', null, InputOption::VALUE_REQUIRED, 'Dependency vendor packages to display in the graph: "vendor-name"', '')
             ->addOption('no-dep-vendors', null, InputOption::VALUE_REQUIRED, 'Dependency vendor packages not to display in the graph: "vendor-name"', '')

             ->addOption('include-tags', null, InputOption::VALUE_REQUIRED, '', '')
             ->addOption('exclude-tags', null, InputOption::VALUE_REQUIRED, '', '')

             ->addOption('separate-graph-packages', null, InputOption::VALUE_REQUIRED, 'Packages for which to create a separate graph', '')
             ->addOption('separate-graph-vendors', null, InputOption::VALUE_REQUIRED, 'Vendors for which to create a separate graph', '')

             ->addOption('no-root-dev-dep', null, InputOption::VALUE_NONE, 'Whether root package require-dev dependencies should be shown')

             ->addOption('default-filtering-mode', null, InputOption::VALUE_REQUIRED, '', 'includes_all')
             ->addOption('default-dep-filtering-mode', null, InputOption::VALUE_REQUIRED, '', 'includes_all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filterConfig = [
            'include_packages' => Utils::getOptionAsArray($input->getOption('packages')),
            'exclude_packages' => Utils::getOptionAsArray($input->getOption('no-packages')),
            'include_vendors' => Utils::getOptionAsArray($input->getOption('vendors')),
            'exclude_vendors' => Utils::getOptionAsArray($input->getOption('no-vendors')),
            'include_dep_packages' => Utils::getOptionAsArray($input->getOption('dep-packages')),
            'exclude_dep_packages' => Utils::getOptionAsArray($input->getOption('no-dep-packages')),
            'include_dep_vendors' => Utils::getOptionAsArray($input->getOption('dep-vendors')),
            'exclude_dep_vendors' => Utils::getOptionAsArray($input->getOption('no-dep-vendors')),
            'include_tags' => Utils::getOptionAsArray($input->getOption('include-tags')),
            'exclude_tags' => Utils::getOptionAsArray($input->getOption('exclude-tags')),
            'separate_graph_packages' => Utils::getOptionAsArray($input->getOption('separate-graph-packages')),
            'separate_graph_vendors' => Utils::getOptionAsArray($input->getOption('separate-graph-vendors')),

            'no_root_dev_dep' => $input->hasOption('no-root-dev-dep'),
            'default_filtering_mode' => $input->getOption('default-filtering-mode'),
            'default_dep_filtering_mode' => $input->getOption('default-dep-filtering-mode'),
        ];

        $graph = new GraphComposer();
        $dgc = new GraphComposerConfigurator($input->getArgument('root-dir'), new PackageFilter($filterConfig), new DependencyAnalyzer);
        $dgc->configure($graph);

        $target = realpath('.') . '/' . $input->getArgument('output');
        if ($target !== null) {
            if (!is_dir($target)) {
                throw new \RuntimeException(sprintf('You must specify a directory. "%s" given.', $target));
            }
        } else {
            $target = rtrim($target, '/') . '/composer-dependency/';
        }

        $format = $input->getOption('format');
        $graph->setFormat($format);

        if ($input->hasOption('separate-graph-packages') || $input->hasOption('separate-graph-vendors')) {
            $pathes = $graph->getImagesPathes($filterConfig['separate_graph_packages'], $filterConfig['separate_graph_vendors']);

            foreach ($pathes as $graphName => $graphPath) {
                $graphName = str_replace('/', '-', $graphName);
                rename($graphPath, sprintf('%s/%s.%s', $target, $graphName, $format));
            }
        }
    }
}
