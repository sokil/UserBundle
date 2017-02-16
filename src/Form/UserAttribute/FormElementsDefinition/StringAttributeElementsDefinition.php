<?php

namespace Sokil\UserBundle\Form\UserAttribute\FormElementsDefinition;

class StringAttributeElementsDefinition extends AbstractAttributeElementsDefinition
{
    /**
     * @return array
     */
    public function getDefinition()
    {
        return [
            'id' => ['type' => 'hidden'],
            'name' => ['type' => 'text'],
            'printFormat' => ['type' => 'text'],
            'defaultValue' => ['type' => 'text'],
            'description' => ['type' => 'text'],
            'translateable' => ['type' => 'check'],
            'defaultValueGetFromCreator' => ['type' => 'check'],
        ];
    }
}