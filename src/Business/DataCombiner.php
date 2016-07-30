<?php


namespace HWai\Business;


use HWai\Objects\Data;
use HWai\Objects\Reference;

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
            $hash = null;
            if ($item instanceof Data) {
                $hash = self::hashForRoute($item->getParts());
            } elseif ($item instanceof Reference) {
                $hash = self::hashForRoute($item->getPath());
            }

            if ($hash !== null) {
                if (isset($result[$hash])) {
                    $value = $result[$hash]->getValue();
                    $value = array_merge($value, $item->getValue());
                    $result[$hash]->setValue($value);
                } else {
                    $result[$hash] = $item;
                }
            }
        }

        return array_values($result);
    }

    /**
     * @param array $parts
     * @return string
     */
    private static function hashForRoute(array $parts)
    {
        return md5(implode('|', array_map(function ($el) {
            return $el;
        }, $parts)));
    }
}