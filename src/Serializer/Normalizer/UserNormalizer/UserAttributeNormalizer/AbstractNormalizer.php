<?php

namespace Sokil\UserBundle\Serializer\Normalizer\UserNormalizer\UserAttributeNormalizer;

use Sokil\UserBundle\Entity\UserAttribute;
use Sokil\UserBundle\Entity\UserAttributeValue;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as EntityManagerRegistry;

abstract class AbstractNormalizer
{
    /**
     * @var EntityManagerRegistry
     */

    protected $entityManagerRegistry;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        EntityManagerRegistry $entityManagerRegistry,
        TranslatorInterface $translator
    ) {
        $this->entityManagerRegistry = $entityManagerRegistry;
        $this->translator = $translator;
    }

    /**
     * Get list of user attributes as array
     *
     * @return array list of converted attributes
     */
    public function normalize(
        UserAttribute $userAttribute,
        UserAttributeValue $userAttributeValue = null
    ) {
        // original value
        if ($userAttributeValue) {
            $originalAttributeValue = $userAttributeValue->getValue();
        } else {
            $originalAttributeValue = null;
        }

        // prepare value
        $value = [];

        // value for view
        $value['view'] = $originalAttributeValue;
        if ($originalAttributeValue) {

            // translate value
            if ($userAttribute->isTranslateable()) {
                $value['view'] = $this->translator->trans($value['view']);
            }

            // format value
            $printFormat = $userAttribute->getPrintFormat();
            if ($printFormat) {
                $value['view'] = $this->renderValue(
                    $printFormat,
                    [
                        'value' => $value['view'],
                    ]
                );
            }
        }

        // value for edit
        $value['edit'] = $originalAttributeValue;

        // common attribute keys
        $normalizedAttributes = [
            'name' => $userAttribute->getName(),
            'label' => $this->translator->trans($userAttribute->getName()),
            'description' => $this->translator->trans($userAttribute->getDescription()),
            'type' => $userAttribute->getType(),
            'value' => $value,
        ];

        return $normalizedAttributes;
    }

    /**
     * Render variables with passed print format
     *
     * @param $printFormat
     * @param array $variables
     * @return mixed
     */
    private function renderValue($printFormat, array $variables)
    {
        return str_replace(
            array_map(
                function($variable) {
                    return '{{' . $variable . '}}';
                },
                array_keys($variables)
            ),
            array_values($variables),
            $printFormat
        );
    }
}