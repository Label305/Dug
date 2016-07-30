<?php


namespace Tests;


use HWai\Objects\Route;

class RouteTest extends TestCase
{

    public function testBuild()
    {
        /* Given */
        $parts = [
            'categories'
        ];

        $callback = function ($pathSet) {

        };

        $otherCallback = function ($pathSet) {

        };

        /* When */
        $route = Route::build($parts, $callback);

        /* Then */
        assertThat($route->getParts(), equalTo($parts));
        assertThat($route->getCallback(), equalTo($callback));
        assertThat($route->getCallback(), not($otherCallback));
    }

}