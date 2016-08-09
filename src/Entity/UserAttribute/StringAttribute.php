<?php

namespace Sokil\UserBundle\Entity\UserAttribute;

use Sokil\UserBundle\Entity\UserAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class StringAttribute extends UserAttribute
{
    public function getType()
    {
        return 'string';
    }

    public function unserializeValue($value)
    {
        return $value ? $value : null;
    }

    public function serializeValue($value)
    {
        return $value ? $value : null;
    }

    /**
     * Get regexp pattern to validate value before save
     * @return string|null
     */
    public function getValidatorPattern()
    {
        return isset($this->meta['pattern'])
            ? $this->meta['pattern']
            : null;
    }
}