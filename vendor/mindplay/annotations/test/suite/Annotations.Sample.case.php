<?php

namespace mindplay\test\Sample;

use mindplay\annotations\Annotation;

/**
 * @usage('class'=>true)
 */
class SampleAnnotation extends Annotation
{
    public $test = 'ok';
}

class DefaultSampleAnnotation extends SampleAnnotation
{

}
