<?php


namespace HWai\Business;


use HWai\Objects\Route;

class RouteMatcher
{

    public static function matches(Route $route, array $request)
    {
        $definition = $route->getParts();
        if (count($definition) != count($request)) {
            return false;
        }

        foreach ($definition as $key => $definitionOfPart) {
            $part = $request[$key];
            if (!self::partMatches('' . $definitionOfPart, !is_array($part) ? [$part] : $part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $definitionOfPart
     * @param array  $part
     * @return bool
     */
    private static function partMatches(string $definitionOfPart, array $part)
    {
        foreach ($part as $item) {
            if (!($definitionOfPart === $item
                || (
                    substr($definitionOfPart, 0, 1) == '/'
                    && preg_match($definitionOfPart, $item)
                ))
            ) {
                return false;
            }
        }

        return true;
    }
}