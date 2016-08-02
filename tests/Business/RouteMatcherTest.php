<?php


namespace Tests\Business;


use Dug\Business\RouteMatcher;
use Dug\Objects\Source;
use Tests\TestCase;

class RouteMatcherTest extends TestCase
{

    public function testSimpleValid()
    {
        /* Given */
        $source = Source::build([
            'categories'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($source, ['categories']);

        /* Then */
        assertThat($result, is(true));
    }

    public function testSimpleNotValid()
    {
        /* Given */
        $source = Source::build([
            'tracks'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($source, ['categories']);

        /* Then */
        assertThat($result, is(false));
    }

    public function testRegexValid()
    {
        /* Given */
        $source = Source::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($source, ['tracks', 123]);

        /* Then */
        assertThat($result, is(true));
    }

    public function testRegexValidWithArray()
    {
        /* Given */
        $source = Source::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($source, ['tracks', [123]]);

        /* Then */
        assertThat($result, is(true));
    }
     
    public function testRegexValidWithMultipleInArray()
    {
        /* Given */
        $source = Source::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($source, ['tracks', [123, 321]]);

        /* Then */
        assertThat($result, is(true));
    }

    public function testRegexValidWithInvalidInArray()
    {
        /* Given */
        $source = Source::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($source, ['tracks', [123, 'asdf']]);

        /* Then */
        assertThat($result, is(false));
    }

    public function testRegexNotValid()
    {
        /* Given */
        $source = Source::build([
            'tracks',
            '/[0-9]+/'
        ], function () {

        });

        /* When */
        $result = RouteMatcher::matches($source, ['tracks', 'length']);

        /* Then */
        assertThat($result, is(false));
    }
}