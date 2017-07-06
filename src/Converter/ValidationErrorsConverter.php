<?php

namespace Sokil\UserBundle\Converter;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Form\FormInterface;

class ValidationErrorsConverter
{
    public function formErrorsToArray(FormInterface $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        /* @var $child \Symfony\Component\Form\Form */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->formErrorsToArray($child);
            }
        }

        return $errors;
    }

    public function constraintViolationListToArray(ConstraintViolationListInterface $list)
    {
        /* @var ConstraintViolationInterface $violation */
        $errors = [];

        foreach($list as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }

}
