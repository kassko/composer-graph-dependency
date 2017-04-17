<?php

namespace Kassko\Composer\GraphDependency\Command;

use Kassko\Composer\GraphDependency\GraphComposer;
use Kassko\Composer\GraphDependency\GraphComposerConfigurator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Export extends Command
{
    protected function configure()
    {
        $this->setName('export')
             ->setDescription('Export dependency graph image for given project directory')
             ->addArgument('dir', InputArgument::OPTIONAL, 'Path to project directory to scan', '.')
             ->addArgument('output', InputArgument::OPTIONAL, 'Path to output image file')

             // add output format option. default value MUST NOT be given, because default is to overwrite with output extension
             ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Image format (svg, png, jpeg)')

             ->addOption('packages', null, InputOption::VALUE_REQUIRED, 'Packages to display in the graph: "vendor-name/package-name"', [])
             ->addOption('no-packages', null, InputOption::VALUE_REQUIRED, 'Packages not to display in the graph: "vendor-name/package-name"', [])

             ->addOption('vendors', null, InputOption::VALUE_REQUIRED, 'Vendor packages to display in the graph: "vendor-name"', [])
             ->addOption('no-vendors', null, InputOption::VALUE_REQUIRED, 'Vendor packages not to display in the graph: "vendor-name"', [])

             ->addOption('dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages to display in the graph: "vendor-name/package-name"', [])
             ->addOption('no-dep-packages', null, InputOption::VALUE_REQUIRED, 'Dependency packages not to display in the graph: "vendor-name/package-name"', [])

             ->addOption('dep-vendors', null, InputOption::VALUE_REQUIRED, 'Dependency vendor packages to display in the graph: "vendor-name"', [])
             ->addOption('no-dep-vendors', null, InputOption::VALUE_REQUIRED, 'Dependency vendor packages not to display in the graph: "vendor-name"', [])

             ->addOption('no-root-dev-dep', null, InputOption::VALUE_NONE, 'Whether root package require-dev dependencies should be shown');
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
                $target = rtrim($target, '/') . '/composer-dependency.svg';
            }

            $filename = basename($target);
            $pos = strrpos($filename, '.');
            if ($pos !== false && isset($filename[$pos + 1])) {
                // extension found and not empty
                $graph->setFormat(substr($filename, $pos + 1));
            }
        }

        $format = $input->getOption('format');
        if ($format !== null) {
            $graph->setFormat($format);
        }

        $path = $graph->getImagePath();

        if ($target !== null) {
            rename($path, $target);
        } else {
            readfile($path);
        }
    }
}
