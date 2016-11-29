<?php

namespace Sokil\UserBundle\Serializer\Normalizer;

use Sokil\UserBundle\Entity\UserAttribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserAttributeNormalizer implements NormalizerInterface
{
    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param object $userAttribute  user attribute to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array|scalar
     */
    public function normalize($userAttribute, $format = null, array $context = array())
    {
        if (!($userAttribute instanceof UserAttribute)) {
            throw new \InvalidArgumentException('User attribute must be instance of ' . UserAttribute::class);
        }

        return [
            'id' => $userAttribute->getId(),
            'name' => $userAttribute->getName(),
            'type' => $userAttribute->getType(),
            'printFormat' => $userAttribute->getPrintFormat(),
            'defaultValue' => $userAttribute->getDefaultValue(),
            'description' => $userAttribute->getDescription(),
            'translateable' => $userAttribute->isTranslateable(),
            'defaultValueGetFromCreator' => $userAttribute->isDefaultValueGetFromCreator(),
        ];
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed  $data   Data to normalize
     * @param string $format The format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}