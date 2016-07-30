<?php


namespace Tests\Business;


use HWai\Business\DataCombiner;
use HWai\Objects\Data;
use HWai\Objects\Reference;
use Tests\TestCase;

class DataCombinerTest extends TestCase
{

    public function testSingle()
    {
        /* Given */

        $data = [
            Data::build(['foo'], ['foo' => 'bar'])
        ];

        /* When */
        $result = DataCombiner::combine($data);

        /* Then */
        assertThat($result, is($data));
    }

    public function testSingleReference()
    {
        /* Given */

        $reference = new Reference(['bar', 1]);
        $data = [
            Data::build(['foo'], $reference)
        ];

        /* When */
        $result = DataCombiner::combine($data);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0]->getValue()[0], is($reference));
    }

    public function testMultiple()
    {
        /* Given */

        $data = [
            Data::build(['foo', 1], ['foo' => 'bar']),
            Data::build(['foo', 2], ['foo' => 'bar2'])
        ];

        /* When */
        $result = DataCombiner::combine($data);

        /* Then */
        assertThat(count($result), is(2));
        assertThat($result[0]->getValue(), is(['foo' => 'bar']));
        assertThat($result[1]->getValue(), is(['foo' => 'bar2']));
    }

    public function testCombineSingle()
    {
        /* Given */

        $data = [
            Data::build(['foo', 1], ['foo' => 'bar']),
            Data::build(['foo', 1], ['name' => 'bar2'])
        ];

        /* When */
        $result = DataCombiner::combine($data);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0]->getValue(), is([
            'foo' => 'bar',
            'name' => 'bar2'
        ]));
    }

    public function testCombineSingleWithReference()
    {
        /* Given */

        $reference = new Reference(['bar', 1]);
        $data = [
            Data::build(['foo', 1], ['foo' => 'bar']),
            Data::build(['foo', 1], $reference)
        ];

        /* When */
        $result = DataCombiner::combine($data);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0]->getValue(), is([
            'foo' => 'bar',
            $reference
        ]));
    }

    public function testCombineMultiple()
    {
        /* Given */

        $data = [
            Data::build(['foo', 1], ['foo' => 'bar']),
            Data::build(['foo', 1], ['name' => 'bar2']),
            Data::build(['foo', 2], ['foo2' => 'bar']),
            Data::build(['foo', 2], ['name2' => 'bar2'])
        ];

        /* When */
        $result = DataCombiner::combine($data);

        /* Then */
        assertThat(count($result), is(2));
        assertThat($result[0]->getValue(), is([
            'foo' => 'bar',
            'name' => 'bar2'
        ]));
        assertThat($result[1]->getValue(), is([
            'foo2' => 'bar',
            'name2' => 'bar2'
        ]));
    }

}