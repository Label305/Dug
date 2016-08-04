<?php


namespace Dug\Business;


use Dug\Objects\Data;
use Dug\Objects\PathProvider;
use Dug\Objects\Reference;

class DataCombiner
{

    /**
     * Combines all available data
     * @param Data[] $data
     * @return Data[]
     */
    public static function combine(array $data)
    {
        /** @var Data[] $result */
        $result = [];

        foreach ($data as $item) {
            $hash = self::toHash($item);
            if ($hash === null) {
                continue;
            }
            if (!isset($result[$hash])) {
                $result[$hash] = Data::build($item->getPath(), []);
            }

            $value = $result[$hash]->getValue();
            $value = self::merge($item, $value);
            $result[$hash]->setValue($value);
        }

        return array_values($result);
    }

    /**
     * @param PathProvider $provider
     * @return string
     */
    private static function toHash(PathProvider $provider)
    {
        return md5(implode('|', array_map(function ($el) {
            return $el;
        }, $provider->getPath())));
    }

    /**
     * @param Data|Reference $item
     * @param array          $target
     * @return array
     */
    private static function merge($item, $target)
    {
        if ($item instanceof Reference) {
            $target[] = $item;
        } else {
            $value = $item->getValue();
            if (is_array($value)) {
                $target = array_merge($target, $value);
            } else {
                $target[] = $value;
            }
        }

        return $target;
    }

}