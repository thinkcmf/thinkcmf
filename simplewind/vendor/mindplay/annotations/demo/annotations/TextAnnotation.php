<?php

/**
 * This file is part of the php-annotation framework.
 *
 * (c) Rasmus Schultz <rasmus@mindplay.dk>
 *
 * This software is licensed under the GNU LGPL license
 * for more information, please see:
 *
 * <https://github.com/mindplay-dk/php-annotations>
 */

namespace mindplay\demo\annotations;


use mindplay\annotations\Annotation;

/**
 * Defines various text (labels, hints, etc.) to be displayed with the annotated property
 *
 * @usage('property'=>true, 'inherited'=>true)
 *
 * @todo implement this
 */
class TextAnnotation extends Annotation
{
    /**
     * @var string The label for the input, typically displayed displayed in front of the input.
     */
    public $label;

    /**
     * @var string An abbreviated version of the input label (for use in column headers or other places where space is limited).
     */
    public $abbr;

    /**
     * @var string A short description of the field, typically displayed on a form together with the input.
     */
    public $info;

    /**
     * @var string Short instructions for the field, typically displayed in a tooltip balloon.
     */
    public $tip;

    /**
     * @var string Long instructions for the field, typically displayed in a popup window.
     */
    public $help;

    /**
     * @var string A short string, typically displayed in the input itself, while empty, or until it receives focus.
     */
    public $watermark;
}
