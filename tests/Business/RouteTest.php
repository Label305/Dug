<?php


namespace Tests\Business;


use Dug\Objects\Source;
use Tests\TestCase;

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
        $source = Source::build($parts, $callback);

        /* Then */
        assertThat($source->getParts(), equalTo($parts));
        assertThat($source->getCallback(), equalTo($callback));
        assertThat($source->getCallback(), not($otherCallback));
    }

}