<?php
namespace Test;

use ArrayObject;

class SomeClass
{
    public function someMethod(ArrayObject $context)
    {
        $context['test2'] = 'def';
    }

    public function someMethod2(ArrayObject $context)
    {
        $context['test3'] = 'qrs';
    }
}
