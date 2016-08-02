<?php


namespace Dug\Exceptions;


class RouteNotFoundException extends DugException
{


    /**
     * RouteNotFoundException constructor.
     */
    public function __construct(array $path)
    {
        parent::__construct('Could not find path: ' . $this->pathToString($path));
    }

    /**
     * @param $path
     * @return string
     */
    private function pathToString($path)
    {
        $result = '';

        foreach ($path as $item) {
            if (is_array($item)) {
                $result .= '[' . $this->pathToString($item) . ']';
            } else {
                $result .= $item;
            }
        }

        return $result;
    }
}