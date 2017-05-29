<?php

namespace Sokil\UserBundle\Converter;

use Doctrine\Bundle\DoctrineBundle\Registry as EntityManagerRegistry;
use Symfony\Component\Translation\TranslatorInterface;

class DiscriminatorMapConverter
{
    /**
     * @var EntityManagerRegistry
     */
    private $entityManagerRegistry;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        EntityManagerRegistry $entityManagerRegistry,
        TranslatorInterface $translator
    ) {
        $this->entityManagerRegistry = $entityManagerRegistry;
        $this->translator = $translator;
    }

    public function getDiscriminatorMap($classname)
    {
        $classMetadata = $this
            ->entityManagerRegistry
            ->getManager()
            ->getClassMetadata($classname);

        $discriminatorMap = [];

        foreach (array_keys($classMetadata->discriminatorMap) as $discriminator) {
            $discriminatorMap[$discriminator] = [
                'label' => $this->translator->trans('user_attribute.field_type.' . $discriminator),
            ];
        }

        return $discriminatorMap;
    }
}