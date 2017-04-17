<?php

namespace Kassko\Composer\GraphDependency;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Attribute\AttributeAware;
use Fhaculty\Graph\Attribute\AttributeBagNamespaced;
use Graphp\GraphViz\GraphViz;

class GraphComposer
{
    private $layoutVertex = array(
        'fillcolor' => '#eeeeee',
        'style' => 'filled, rounded',
        'shape' => 'box',
        'fontcolor' => '#314B5F'
    );

    private $layoutVertexRoot = array(
        'style' => 'filled, rounded, bold'
    );

    private $layoutEdge = array(
        'fontcolor' => '#767676',
        'fontsize' => 10,
        'color' => '#1A2833'
    );

    private $layoutEdgeDev = array(
        'style' => 'dashed'
    );

    private $dependencyGraph;

    /**
     * @var GraphViz
     */
    private $graphviz;

    /**
     * @param GraphViz|null $graphviz
     */
    public function __construct(GraphViz $graphviz = null)
    {
        if ($graphviz === null) {
            $graphviz = new GraphViz();
            $graphviz->setFormat('svg');
        }
        $this->graphviz = $graphviz;
    }

    public function setDependencyGraph(array $dependencyGraph)
    {
        $this->dependencyGraph = $dependencyGraph;
    }

    /**
     * @param array $separateGraphPackagesNames
     * @param array $separateGraphVendorNames
     *
     * @return Graph[]
     */
    public function createGraphes(array $separateGraphPackagesNames = [], array $separateGraphVendorNames = [])
    {
        if (!count($separateGraphPackagesNames) && !count($separateGraphVendorNames)) {
            $graph = new Graph();

            foreach ($this->dependencyGraph->getPackages() as $package) {
                $this->populateGraph($graph, $package);
            }

            return [$graph];
        }

        $graphes = [];

        foreach ($this->dependencyGraph->getPackages() as $package) {
            $packageFullName = $package->getName();

            if (in_array($packageFullName, $separateGraphPackagesNames)) {
                if(!isset($graphes[$packageFullName])) {
                    $graphes[$packageFullName] = new Graph;
                }
                $this->populateGraph($graphes[$packageFullName], $package);
            }

            list($vendorName, $packageName) = explode('/', $packageFullName, 2);
            if (in_array($vendorName, $graphes)) {
                if(!isset($graphes[$vendorName])) {
                    $graphes[$vendorName] = new Graph;
                }
                $this->populateGraph($graphes[$vendorName], $package);
            }
        }

        return $graphes;
    }

    protected function populateGraph(Graph $graph, $package)
    {
        $name = $package->getName();
        $start = $graph->createVertex($name, true);

        $label = $name;
        if ($package->getVersion() !== null) {
            $label .= ': ' . $package->getVersion();
        }

        $this->setLayout($start, array('label' => $label) + $this->layoutVertex);

        foreach ($package->getOutEdges() as $requires) {
            $targetName = $requires->getDestPackage()->getName();
            $target = $graph->createVertex($targetName, true);

            $label = $requires->getVersionConstraint();

            $edge = $start->createEdgeTo($target);
            $this->setLayout($edge, array('label' => $label) + $this->layoutEdge);

            if ($requires->isDevDependency()) {
                $this->setLayout($edge, $this->layoutEdgeDev);
            }
        }

        $root = $graph->getVertex($this->dependencyGraph->getRootPackage()->getName());
        $this->setLayout($root, $this->layoutVertexRoot);
    }

    public function displayGraph()
    {
        $graphes = $this->createGraph();

        $this->graphviz->display($graphes[0]);
    }

    public function getImagePath()
    {
        $graphes = $this->createGraphes();

        return $this->graphviz->createImageFile(current($graphes));
    }

    public function getImagesPathes(array $separateGraphPackagesNames = [], array $separateGraphVendorNames = [])
    {
        $graphes = $this->createGraphes($separateGraphPackagesNames, $separateGraphVendorNames);

        $imagesFiles = [];
        foreach ($graphes as $name => $graph) {
            $imagesFiles[$name] = $this->graphviz->createImageFile($graph);
        }

        return $imagesFiles;
    }

    public function setFormat($format)
    {
        $this->graphviz->setFormat($format);

        return $this;
    }

    protected function setLayout(AttributeAware $entity, array $layout)
    {
        $bag = new AttributeBagNamespaced($entity->getAttributeBag(), 'graphviz.');
        $bag->setAttributes($layout);

        return $entity;
    }
}
