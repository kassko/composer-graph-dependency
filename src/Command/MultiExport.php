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
        $this->setName('multi_export')
             ->setDescription('Export dependency graph images for given project directory and given packages or vendors')
             ->addArgument('root-dir', InputArgument::OPTIONAL, 'Path to project directory to scan', realpath('.'))
             ->addArgument('output', InputArgument::OPTIONAL, 'Directory to output images files.', './composer-dependency')

             // add output format option. default value MUST NOT be given, because default is to overwrite with output extension
             ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Image format (svg, png, jpeg)', 'svg')

             ->addOption('packages', null, InputOption::VALUE_REQUIRED, 'Packages to display in the graph: "vendor-name/package-name"', '')
             ->addOption('no-packages', null, InputOption::VALUE_REQUIRED, 'Packages not to display in the graph: "vendor-name/package-name"', '')

             ->addOption('dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages to display in the graph: "vendor-name/package-name"', '')
             ->addOption('no-dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages not to display in the graph: "vendor-name/package-name"', '')

             ->addOption('tags', null, InputOption::VALUE_REQUIRED, '', '')
             ->addOption('no-tags', null, InputOption::VALUE_REQUIRED, '', '')
             ->addOption('dep-tags', null, InputOption::VALUE_REQUIRED, '', '')
             ->addOption('dep-no-tags', null, InputOption::VALUE_REQUIRED, '', '')

             ->addOption('separate-graph-packages', null, InputOption::VALUE_REQUIRED, 'Packages for which to create a separate graph', '')
             ->addOption('separate-graph-vendors', null, InputOption::VALUE_REQUIRED, 'Vendors for which to create a separate graph', '')
             ->addOption('separate-graph-vendors-packages', null, InputOption::VALUE_REQUIRED, 'Vendors for which to create a separate graph', '')

             ->addOption('no-root-dev-dep', null, InputOption::VALUE_NONE, 'Whether root package require-dev dependencies should be shown')

             ->addOption('default-filtering-mode', null, InputOption::VALUE_REQUIRED, '', 'includes_all')
             ->addOption('default-dep-filtering-mode', null, InputOption::VALUE_REQUIRED, '', 'includes_all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filterConfig = [
            'packages' => Utils::getOptionAsArray($input->getOption('packages')),
            'no_packages' => Utils::getOptionAsArray($input->getOption('no-packages')),
            'dep_packages' => Utils::getOptionAsArray($input->getOption('dep-packages')),
            'no_dep_packages' => Utils::getOptionAsArray($input->getOption('no-dep-packages')),
            'tags' => Utils::getOptionAsArray($input->getOption('tags')),
            'no-tags' => Utils::getOptionAsArray($input->getOption('no-tags')),
            'dep-tags' => Utils::getOptionAsArray($input->getOption('dep-tags')),
            'no-dep-tags' => Utils::getOptionAsArray($input->getOption('no-dep-tags')),
            'separate_graph_packages' => Utils::getOptionAsArray($input->getOption('separate-graph-packages')),
            'separate_graph_vendors' => Utils::getOptionAsArray($input->getOption('separate-graph-vendors')),
            'separate_graph_vendors_packages' => Utils::getOptionAsArray($input->getOption('separate-graph-vendors-packages')),

            'no_root_dev_dep' => $input->hasOption('no-root-dev-dep'),
            'default_filtering_mode' => $input->getOption('default-filtering-mode'),
            'default_dep_filtering_mode' => $input->getOption('default-dep-filtering-mode'),
        ];

        $graph = new GraphComposer();
        $dgc = new GraphComposerConfigurator($input->getArgument('root-dir'), new PackageFilter($filterConfig), new DependencyAnalyzer);
        $dgc->configure($graph);

        $target = realpath('.') . '/' . $input->getArgument('output');
        if ($target !== null) {
        } else {
            $target = rtrim($target, '/') . '/composer-dependency/';
        }

        if (!is_dir($target)) {
            mkdir($target, 0777, true);
            //throw new \RuntimeException(sprintf('You must specify a directory. "%s" given.', $target));
        }

        $format = $input->getOption('format');
        $graph->setFormat($format);

        if (
            $input->hasOption('separate-graph-packages')
            || $input->hasOption('separate-graph-vendors')
            || $input->hasOption('separate-graph-vendors-packages')
        ) {
            $pathes = $graph->getImagesPathes(
                $filterConfig['separate_graph_packages'],
                $filterConfig['separate_graph_vendors'],
                $filterConfig['separate_graph_vendors_packages']
            );

            foreach ($pathes as $graphName => $graphPath) {
                $graphName = str_replace('/', '-', $graphName);

                $src = $graphPath;
                $dest = sprintf('%s/%s.%s', $target, $graphName, $format);

                /*$src = substr($src, 1);
                $dest = substr($dest, 1);*/

                rename($src, $dest);
            }
        }
    }
}
