<?php


namespace Tests\Business;


use HWai\Business\RouteMatcher;
use HWai\Objects\Route;
use Tests\TestCase;

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

    public function testRegexValidWithArray()
    {
        /* Given */
        $route = Route::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($route, ['tracks', [123]]);

        /* Then */
        assertThat($result, is(true));
    }
     
    public function testRegexValidWithMultipleInArray()
    {
        /* Given */
        $route = Route::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($route, ['tracks', [123, 321]]);

        /* Then */
        assertThat($result, is(true));
    }

    public function testRegexValidWithInvalidInArray()
    {
        /* Given */
        $route = Route::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($route, ['tracks', [123, 'asdf']]);

        /* Then */
        assertThat($result, is(false));
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