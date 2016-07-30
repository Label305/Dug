<?php


namespace HWai\Business;


use HWai\Objects\Data;
use HWai\Objects\Reference;
use HWai\Objects\ReferenceToSingle;
use HWai\Router;

class ReferenceResolver
{
    /**
     * @var Router
     */
    private $router;

    /**
     * ReferenceResolver constructor.
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param array $data
     * @return array
     */
    public function process(array $data):array
    {
        $references = $this->collectReferences($data);
        $groupedReferences = $this->groupReferences($references);
        foreach ($groupedReferences as $group) {
            $path = $this->referencesToPath($group);
            $dataForGroup = $this->router->data($path);
            $data = $this->mergeDataForReferences($group, $data, $dataForGroup);
        }

        return $data;
    }

    /**
     * Collects all references
     * @param array $data
     * @return array
     */
    private function collectReferences(array $data):array
    {
        $references = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                $references = array_merge($references, $this->collectReferences($item));
            } elseif ($item instanceof Data) {
                $references = array_merge($references, $this->collectReferences($item->getValue()));
            } elseif ($item instanceof Reference) {
                $references[] = $item;
            }
        }

        return $references;
    }

    /**
     * @param Reference[] $references
     * @return Reference[][]
     */
    private function groupReferences(array $references):array
    {
        $groups = [];
        foreach ($references as $reference) {
            $route = $this->router->routeForPath($reference->getPath());
            $hash = $route->toString();
            if (!isset($groups[$hash])) {
                $groups[$hash] = [];
            }
            $groups[$hash][] = $reference;
        }

        return array_values($groups);
    }

    /**
     * @param Reference[] $references
     * @return array
     */
    private function referencesToPath(array $references):array
    {
        //Get reference path
        $path = $references[0]->getPath();

        //Make sure every element in the path is an array
        foreach ($path as $key => $item) {
            if (!is_array($item)) {
                $path[$key] = [$item];
            }
        }

        //Merge all path parts
        foreach ($references as $item) {
            $parts = $item->getPath();
            foreach ($parts as $index => $part) {
                $path[$index][] = $part;
            }
        }

        //Normalize so that single item is passed as single item
        foreach ($path as $key => $item) {
            $path[$key] = array_unique($item);
            if (count($path[$key]) === 1) {
                $path[$key] = $path[$key][0];
            }
        }

        return $path;
    }

    /**
     * @param Reference[] $references
     * @param array       $data
     * @param array       $fetched
     * @return array
     */
    private function mergeDataForReferences(array $references, array $data, array $fetched)
    {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $data[$key] = $this->mergeDataForReferences($references, $item, $fetched);
            } elseif ($item instanceof Data) {
                $data[$key] = $this->mergeDataForReferences($references, $item->getValue(), $fetched);
            } elseif ($item instanceOf Reference) {
                $data[$key] = $this->findDataForReference($item, $fetched);
            }
        }

        return $data;
    }

    /**
     * @param Reference $reference
     * @param Data[]    $fetched
     * @return Data|null
     */
    private function findDataForReference(Reference $reference, array $fetched)
    {
        foreach ($fetched as $item) {
            $path = $item->getPath();
            if (count($path) == count($reference->getPath())) {
                $isSame = true;
                for ($i = 0; $i < count($path) && $isSame; $i++) {
                    if ($path[$i] != $reference->getPath()[$i]) {
                        $isSame = false;
                    }
                }

                if ($isSame) {
                    return $item;
                }
            }
        }

        return null;
    }
}