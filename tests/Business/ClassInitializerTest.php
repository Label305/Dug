<?php


namespace Tests\Business;


use Dug\Dug;
use Dug\Interfaces\ClassInitializer;
use Dug\Objects\Source;
use Tests\Helpers\UserProvider;
use Tests\TestCase;

class ClassInitializerTest extends TestCase
{

    public function testClassInitializer()
    {
        /* Given */
        $dug = new Dug();
        $classInitializer = new ClassInitializerSpy();
        $dug->setClassInitializer($classInitializer);

        $source = Source::build(['users', '/[0-9]+/'], UserProvider::class);
        $dug->register($source);

        /* When */
        $dug->fetch(['users', 1]);

        /* Then */
        assertThat($classInitializer->initializedClass, is(UserProvider::class));
    }

}

class ClassInitializerSpy implements ClassInitializer
{

    public $initializedClass;

    /**
     * @param string $className
     * @return mixed
     */
    public function instantiate(string $className)
    {
        $this->initializedClass = $className;

        return new $className;
    }
}

