<?php


namespace HWai\Business;


use HWai\Exceptions\RouteNotFoundException;
use HWai\Objects\Data;
use HWai\Objects\Reference;
use HWai\Objects\ReferenceToSingle;
use HWai\Objects\Route;
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
     * @param Data[] $data
     * @return array
     */
    public function process(array $data):array
    {
        $references = $this->extractReferences($data);
        $grouped = $this->groupReferences($references);

        foreach ($grouped as $group) {
            $combinedPath = $this->getPathForGroup($group);
            $dataForGroup = $this->router->data($combinedPath);
            $route = $this->router->routeForPath($combinedPath);
            $data = $this->replaceReferencesForRouteWithData($route, $data, $dataForGroup);
        }

        return $data;
    }

    /**
     * @param $value
     * @return mixed
     */
    private function fetch($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->fetch($item);
            }
        } elseif ($value instanceof Reference) {
            $value = $this->router->data($value->getPath());
        }

        return $value;
    }

    /**
     * @param $data
     * @return array
     */
    private function extractReferences($data):array
    {
        $result = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                $result = array_merge($result, $this->extractReferences($item));
            }
        } elseif ($data instanceof Data) {
            $result = $this->extractReferences($data->getValue());
        } elseif ($data instanceof Reference) {
            $result = [$data];
        }

        return $result;
    }

    /**
     * @param Reference[] $references
     * @return array
     */
    private function groupReferences(array $references)
    {
        $groups = [];
        foreach ($references as $reference) {
            $route = $this->router->routeForPath($reference->getPath());

            if ($route === null) {
                throw new RouteNotFoundException();
            }

            if (!isset($groups[$route->toString()])) {
                $groups[$route->toString()] = [];
            }

            $groups[$route->toString()][] = $reference;
        }

        return array_values($groups);
    }

    /**
     * @param Reference[] $group
     * @return array
     */
    private function getPathForGroup(array $group):array
    {
        $path = $group[0]->getPath();
        foreach ($path as $key => $part) {
            if (!is_array($part)) {
                $path[$key] = [$part];
            }
        }

        foreach ($group as $reference) {
            if ($reference === $group[0]) {
                continue;
            }

            foreach ($reference->getPath() as $key => $part) {
                $path[$key][] = $part;
            }
        }

        foreach ($path as $key => $part) {
            $part = array_unique($part);
            if (count($part) === 1) {
                $path[$key] = $part[0];
            } else {
                $path[$key] = $part;
            }
        }

        return $path;
    }

    /**
     * @param Route $route
     * @param array $data
     * @param array $replacementData
     * @todo this might need some refactoring ;-)
     * @return array
     */
    private function replaceReferencesForRouteWithData(Route $route, array $data, array $replacementData):array
    {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $data[$key] = $this->replaceReferencesForRouteWithData($route, $item, $replacementData);
            } elseif ($item instanceof Data) {
                $value = $item->getValue();
                if (is_array($value)) {
                    $item->setValue($this->replaceReferencesForRouteWithData($route, $value, $replacementData));
                } elseif ($value instanceof Reference) {
                    if ($value instanceof ReferenceToSingle) {
                        $data[$key] = $this->findReplacement($replacementData, $value);
                    } else {
                        $replacement = $this->findReplacements($replacementData, $value);
                        $item->setValue($replacement !== null ? $replacement->getValue() : null);
                    }
                }
            } elseif ($item instanceof Reference) {
                $data[$key] = $item instanceof ReferenceToSingle
                    ? $this->findReplacement($replacementData, $item)
                    : $this->findReplacements($replacementData, $item);
            }
        }

        return $data;
    }

    /**
     * @param Data[]    $replacementData
     * @param Reference $item
     * @return mixed|null
     */
    private function findReplacements(array $replacementData, Reference $item)
    {
        $replacements = [];
        foreach ($replacementData as $replacementItem) {
            if ($this->pathContains($item->getPath(), $replacementItem->getPath())) {
                $replacements[] = $replacementItem;
            }
        }

        return $replacements;
    }

    /**
     * @param Data[]    $replacementData
     * @param Reference $item
     * @return Data|null
     */
    private function findReplacement(array $replacementData, Reference $item)
    {
        $replacements = $this->findReplacements($replacementData, $item);

        return isset($replacements[0]) ? $replacements[0] : null;
    }

    /**
     * @param array $source
     * @param array $child
     * @return bool
     */
    private function pathContains(array $source, array $child):bool
    {
        if (count($source) != count($child)) {
            return false;
        }

        for ($i = 0; $i < count($source); $i++) {
            if (
                $source[$i] != $child[$i]
                && (!is_array($source[$i]) || !in_array($child[$i], $source[$i]))
            ) {
                return false;
            }
        }

        return true;
    }

}