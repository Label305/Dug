<?php


namespace Tests;


use HWai\PathSet;
use HWai\Route;

class RouteTest extends TestCase
{

    public function testBuild()
    {
        /* Given */
        $parts = [
            'categories'
        ];

        $callback = function (PathSet $pathSet) {

        };

        $otherCallback = function (PathSet $pathSet) {

        };

        /* When */
        $route = Route::build($parts, $callback);

        /* Then */
        assertThat($route->getParts(), equalTo($parts));
        assertThat($route->getCallback(), equalTo($callback));
        assertThat($route->getCallback(), not($otherCallback));
    }

}