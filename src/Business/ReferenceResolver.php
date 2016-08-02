<?php


namespace Dug\Business;


use Dug\Exceptions\RouteNotFoundException;
use Dug\Objects\Data;
use Dug\Objects\Reference;
use Dug\Objects\ReferenceToSingle;
use Dug\Dug;

class ReferenceResolver
{
    /**
     * @var Dug
     */
    private $dug;

    /**
     * ReferenceResolver constructor.
     */
    public function __construct(Dug $dug)
    {
        $this->dug = $dug;
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
            $dataForGroup = $this->dug->data($combinedPath);
            $data = $this->substitueReferences($data, $dataForGroup);
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
            $value = $this->dug->data($value->getPath());
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
            $source = $this->dug->sourceForPath($reference->getPath());

            if ($source === null) {
                throw new RouteNotFoundException($reference->getPath());
            }

            if (!isset($groups[$source->toString()])) {
                $groups[$source->toString()] = [];
            }

            $groups[$source->toString()][] = $reference;
        }

        return array_values($groups);
    }

    /**
     * @param Reference[] $group
     * @return array
     */
    private function getPathForGroup(array $group):array
    {
        $path = [];
        foreach ($group as $reference) {
            $path = $this->extendPath($path, $reference->getPath());
        }

        return $this->cleanPath($path);
    }

    /**
     * @param array $substitutions
     * @return array
     */
    private function substitueReferences(array $data, array $substitutions):array
    {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $data[$key] = $this->substitueReferences($item, $substitutions);
            }
            if ($item instanceof Data) {
                $value = $item->getValue();
                if (is_array($value)) {
                    $item->setValue($this->substitueReferences($value, $substitutions));
                } elseif ($value instanceof Reference) {
                    $data[$key] = $this->replacementForReference($substitutions, $data[$key]);
                }
            }
            if ($item instanceof Reference) {
                $data[$key] = $this->replacementForReference($substitutions, $item);
            }
        }

        return $data;
    }

    /**
     * @param array $replacementData
     * @param       $item
     * @return Data|Data[]
     */
    private function replacementForReference(array $replacementData, Reference $item)
    {
        return $item instanceof ReferenceToSingle
            ? $this->findReplacement($replacementData, $item)
            : $this->findReplacements($replacementData, $item);
    }

    /**
     * @param Data[]    $replacementData
     * @param Reference $item
     * @return Data[]
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

    /**
     * Remove duplicates and clean everything up nicely
     * @param array $path
     * @return array
     */
    private function cleanPath(array $path):array
    {
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
     * @param $extension
     * @param $base
     * @return mixed
     */
    private function extendPath($base, $extension)
    {
        foreach ($extension as $key => $part) {
            if (!isset($base[$key])) {
                $base[$key] = [];
            }
            $base[$key][] = $part;
        }

        return $base;
    }

}