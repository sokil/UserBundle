<?php

namespace Sokil\UserBundle\Entity\UserAttribute;

use Sokil\UserBundle\Entity\UserAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EntityAttribute extends UserAttribute
{
    public function getEntityClass()
    {
        return $this->meta['class'];
    }

    public function getEntityManagerName()
    {
        return isset($this->meta['entityManager']) ? $this->meta['entityManager'] : null;
    }

    public function getType()
    {
        return 'entity';
    }

    public function unserializeValue($value)
    {
        return $value ? (int) $value : null;
    }

    public function serializeValue($value)
    {
        return $value ? (int) $value : null;
    }
}