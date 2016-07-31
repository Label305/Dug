<?php


namespace HWai;

use HWai\Business\DataCombiner;
use HWai\Business\ReferenceResolver;
use HWai\Business\RouteMatcher;
use HWai\Exceptions\RouteNotFoundException;
use HWai\Objects\Data;
use HWai\Objects\Route;

class Router
{

    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @param Route $route
     */
    public function register(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @param array $path
     * @return mixed
     */
    public function fetch(array $path)
    {
        $data = $this->data($path);

        return $this->dataToArray($data);
    }

    /**
     * @param $path
     * @return mixed|null
     */
    public function fetchSingle($path)
    {
        $result = $this->fetch($path);

        return isset($result[0]) ? $result[0] : null;
    }


    /**
     * @param array $path
     * @return Data[]
     */
    public function data(array $path)
    {
        $route = $this->routeForPath($path);
        if ($route === null) {
            throw new RouteNotFoundException($path);
        }

        $combined = DataCombiner::combine($route->getCallback()->call($route, $path));

        return (new ReferenceResolver($this))->process($combined);
    }

    /**
     * @param array $path
     * @return Route|null
     */
    public function routeForPath(array $path)
    {
        foreach ($this->routes as $route) {
            if (RouteMatcher::matches($route, $path)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function dataToArray($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->dataToArray($item);
            }
        }
        if ($data instanceof Data) {
            $data = $this->dataToArray($data->getValue());
        }

        return $data;
    }

}