<?php

namespace Sokil\UserBundle\Serializer\Normalizer\UserNormalizer\UserAttributeNormalizer;

use Sokil\UserBundle\Entity\UserAttribute;
use Sokil\UserBundle\Entity\UserAttributeValue;

class EntityNormalizer extends AbstractNormalizer
{
    public function normalize(
        UserAttribute $userAttribute,
        UserAttributeValue $userAttributeValue = null
    ) {
        $normalizedAttributes = parent::normalize(
            $userAttribute,
            $userAttributeValue
        );

        // get available options
        $entityRepository = $this->entityManagerRegistry
            ->getRepository(
                $userAttribute->getEntityClass(),
                $userAttribute->getEntityManagerName()
            );

        // set available options
        $normalizedAttributes['options'] = [];

        foreach ($entityRepository->findAll() as $entity) {
            $normalizedAttributes['options'][$entity->getId()] = [
                'value' => $entity->getId(),
                'text' => (string) $entity,
            ];

            // change entity with entity value
            if ($entity->getId() === $normalizedAttributes['value']['edit']) {
                $normalizedAttributes['value']['view'] = (string) $entity;
            }
        }

        return $normalizedAttributes;
    }
}