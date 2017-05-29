<?php

namespace Sokil\UserBundle\Serializer\Normalizer;

use Sokil\UserBundle\Entity\Group;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RoleGroupNormalizer implements NormalizerInterface
{
    public function normalize($roleGroup, $format = null, array $context = array())
    {
        if (!($roleGroup instanceof Group)) {
            throw new \InvalidArgumentException('Group must be instance of ' . Group::class);
        }

        return [
            'id' => $roleGroup->getId(),
            'name' => $roleGroup->getName(),
            'roles' => $roleGroup->getRoles(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}