<?php

namespace Sokil\UserBundle\Form\UserAttribute\FormElementsDefinition;

abstract class AbstractAttributeElementsDefinition
{
    /**
     * @return array
     */
    abstract public function getDefinition();
}