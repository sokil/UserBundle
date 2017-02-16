<?php

namespace Sokil\UserBundle\Form\UserAttribute;

use Sokil\UserBundle\Form\UserAttribute\FormElementsDefinition\AbstractAttributeElementsDefinition;

class FormElementsDefinitionBuilder
{
    /**
     * @param string $userAttributeType
     * @return AbstractAttributeElementsDefinition
     */
    public function getFormElementsDefinition($userAttributeType)
    {
        // get class name
        $namespace = '\Sokil\UserBundle\Form\UserAttribute\FormElementsDefinition\\';
        $className = ucfirst(strtolower($userAttributeType)) . 'AttributeElementsDefinition';
        $fullyQualifiedClassName = $namespace . $className;

        // build class
        return new $fullyQualifiedClassName;
    }
}