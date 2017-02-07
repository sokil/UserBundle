<?php

namespace Sokil\UserBundle\CommandBus\CreateUser;

use Sokil\UserBundle\CommandBus\ManageUser\AbstractCommandHandler;
use Sokil\UserBundle\CommandBus\ManageUser\CreateUserCommand;
use Sokil\UserBundle\Entity\User;
use Sokil\UserBundle\Entity\UserAttribute;
use Sokil\UserBundle\Entity\UserAttributeValue;

class CreateUserCommandHandler extends AbstractCommandHandler
{
    /**
     * @param User $user
     * @return void
     */
    public function onBeforeModifyUser($user)
    {
        // set activated
        $user->setEnabled(true);
    }

    /**
     * @param $attributeId
     * @param UserAttribute $attribute
     * @param User $user
     */
    protected function onEmptyPassedUserAttributeValue(
        $attributeId,
        UserAttribute $attribute,
        User $user
    ) {
        // get default value
        $defaultValue = $attribute->getDefaultValue();
        if (!$defaultValue && $attribute->isDefaultValueGetFromCreator() && $this->currentUser) {
            $currentUserAttributeValues = $this->currentUser->getAttributeValues();
            if (isset($currentUserAttributeValues[$attributeId])) {
                $defaultValue = $currentUserAttributeValues[$attributeId]->getValue();
            }
        }
        // persis default value
        if ($defaultValue) {
            $user->addAttributeValue(new UserAttributeValue(
                $user,
                $this->entityManager->getReference('UserBundle:UserAttribute', $attributeId),
                $defaultValue
            ));
        }
    }

    /**
     * @param object $command
     * @return bool
     */
    public function supports($command)
    {
        return $command instanceof CreateUserCommand;
    }
}