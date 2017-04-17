<?php

namespace Kassko\Composer\GraphDependency\Command;

use Kassko\Composer\GraphDependency\GraphComposer;
use Kassko\Composer\GraphDependency\GraphComposerConfigurator;
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
             ->addArgument('dir', InputArgument::OPTIONAL, 'Path to project directory to scan', '.')
             ->addArgument('output', InputArgument::OPTIONAL, 'Directory to output images files.', 'composer-dependency')

             // add output format option. default value MUST NOT be given, because default is to overwrite with output extension
             ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Image format (svg, png, jpeg)', 'svg')

             ->addOption('packages', null, InputOption::VALUE_REQUIRED, 'Packages to display in the graph: "vendor-name/package-name"', [])
             ->addOption('no-packages', null, InputOption::VALUE_REQUIRED, 'Packages not to display in the graph: "vendor-name/package-name"', [])

             ->addOption('vendors', null, InputOption::VALUE_REQUIRED, 'Vendor packages to display in the graph: "vendor-name"', [])
             ->addOption('no-vendors', null, InputOption::VALUE_REQUIRED, 'Vendor packages not to display in the graph: "vendor-name"', [])

             ->addOption('dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages to display in the graph: "vendor-name/package-name"', [])
             ->addOption('no-dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages not to display in the graph: "vendor-name/package-name"', [])

             ->addOption('dep-vendors', null, InputOption::VALUE_REQUIRED, 'Dependency vendor packages to display in the graph: "vendor-name"', [])
             ->addOption('no-dep-vendors', null, InputOption::VALUE_REQUIRED, 'Dependency vendor packages not to display in the graph: "vendor-name"', [])

             ->addOption('no-root-dev-dep', null, InputOption::VALUE_NONE, 'Whether root package require-dev dependencies should be shown')

             ->addOption('separate-graph-packages', null, InputOption::VALUE_REQUIRED, 'Packages for which to create a separate graph', []);
             ->addOption('separate-graph-vendors', null, InputOption::VALUE_REQUIRED, 'Vendors for which to create a separate graph', [])
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filterConfig = [
            'include-packages' => $input->getArgument('packages'),
            'exclude_packages' => $input->getArgument('no-packages'),
            'include-vendors' => $input->getArgument('vendors'),
            'exclude-vendors' => $input->getArgument('no-vendors'),
            'include-dep-packages' => $input->getArgument('dep-packages'),
            'exclude-dep-packages' => $input->getArgument('no-dep-packages'),
            'include-dep-vendors' => $input->getArgument('dep-vendors'),
            'exclude-dep-vendors' => $input->getArgument('no-dep-vendors'),
            'no-root-dev-dep' => $input->hasOption('no-root-dev-dep'),
        ];

        $graph = new GraphComposer();
        $dgc = GraphComposerConfigurator($input->getArgument('dir'), new PackageFilter($filterConfig), new DependencyAnalyzer);
        $dgc->configure($graph);

        $target = $input->getArgument('output');
        if ($target !== null) {
            if (is_dir($target)) {
                throw new \RuntimeException(sprintf('You must specify a directory. "%s" given.', $target));
            }
        } else {
            $target = rtrim($target, '/') . '/composer-dependency/';
        }

        $format = $input->getOption('format');
        $graph->setFormat($format);

        $pathes = $graph->getImagesPathes($input->getArgument('separate-graph-packages'), $input->getArgument('separate-graph-vendors'));

        foreach ($pathes as $graphName => $graphPath) {
            $graphName = str_replace('/', '-', $graphName);
            rename($graphPath, sprintf('%s/%s.%s', $target, $graphName, $format);
        }
    }
}
