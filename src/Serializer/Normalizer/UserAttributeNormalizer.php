<?php

namespace Sokil\UserBundle\Serializer\Normalizer;

use Sokil\UserBundle\Entity\UserAttribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserAttributeNormalizer implements NormalizerInterface
{
    const SERIALIZATION_GROUP_FORM = 'form';

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param object $userAttribute  user attribute to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array
     */
    public function normalize($userAttribute, $format = null, array $context = array())
    {
        if (!($userAttribute instanceof UserAttribute)) {
            throw new \InvalidArgumentException('User attribute must be instance of ' . UserAttribute::class);
        }

        // normalization groups
        $normalizationGroups = (!empty($context['groups']) && is_array($context['groups']))
            ? $context['groups']
            : [];

        // prepare attribute
        $attribute = array_filter([
            'id' => $userAttribute->getId(),
            'name' => $userAttribute->getName(),
            'type' => $userAttribute->getType(),
            'printFormat' => $userAttribute->getPrintFormat(),
            'defaultValue' => $userAttribute->getDefaultValue(),
            'description' => $userAttribute->getDescription(),
            'translateable' => $userAttribute->isTranslateable(),
            'defaultValueGetFromCreator' => $userAttribute->isDefaultValueGetFromCreator(),
        ]);

        // schema
        if (in_array(self::SERIALIZATION_GROUP_FORM, $normalizationGroups)) {
            $attribute['form'] = [
                'id' => ['type' => 'hidden'],
                'name' => ['type' => 'text'],
                'printFormat' => ['type' => 'text'],
                'defaultValue' => ['type' => 'text'],
                'description' => ['type' => 'text'],
                'translateable' => ['type' => 'check'],
                'defaultValueGetFromCreator' => ['type' => 'check'],
            ];
        }

        return $attribute;
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