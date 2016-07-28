<?php


namespace Tests;


use HWai\Route;
use HWai\RouteMatcher;

class RouteMatcherTest extends TestCase
{

    public function testSimpleValid()
    {
        /* Given */
        $route = Route::build([
            'categories'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($route, ['categories']);

        /* Then */
        assertThat($result, is(true));
    }

    public function testSimpleNotValid()
    {
        /* Given */
        $route = Route::build([
            'tracks'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($route, ['categories']);

        /* Then */
        assertThat($result, is(false));
    }

    public function testRegexValid()
    {
        /* Given */
        $route = Route::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($route, ['tracks', 123]);

        /* Then */
        assertThat($result, is(true));
    }

    public function testRegexNotValid()
    {
        /* Given */
        $route = Route::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($route, ['tracks', 'length']);

        /* Then */
        assertThat($result, is(false));
    }
}