<?php


namespace Tests\Business;


use HWai\Business\DataCombiner;
use HWai\Objects\Data;
use Tests\TestCase;

class DataCombinerTest extends TestCase
{

    public function testSingle()
    {
        /* Given */

        $data = [
            Data::build(
                [
                    'foo'
                ],
                [
                    'foo' => 'bar'
                ]
            )
        ];

        /* When */
        $result = DataCombiner::combine($data);

        /* Then */
        assertThat($result, is($data));
    }

    public function testMultiple()
    {
        /* Given */

        $data = [
            Data::build(
                [
                    'foo',
                    1
                ],
                [
                    'foo' => 'bar'
                ]
            ),
            Data::build(
                [
                    'foo',
                    2
                ],
                [
                    'foo' => 'bar2'
                ]
            )
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
            Data::build(
                [
                    'foo',
                    1
                ],
                [
                    'foo' => 'bar'
                ]
            ),
            Data::build(
                [
                    'foo',
                    1
                ],
                [
                    'name' => 'bar2'
                ]
            )
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

    public function testCombineMultiple()
    {
        /* Given */

        $data = [
            Data::build(
                [
                    'foo',
                    1
                ],
                [
                    'foo' => 'bar'
                ]
            ),
            Data::build(
                [
                    'foo',
                    1
                ],
                [
                    'name' => 'bar2'
                ]
            ),
            Data::build(
                [
                    'foo',
                    2
                ],
                [
                    'foo2' => 'bar'
                ]
            ),
            Data::build(
                [
                    'foo',
                    2
                ],
                [
                    'name2' => 'bar2'
                ]
            )
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