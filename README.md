# UserBundle

User groups and roles management

[![Latest Stable Version](https://poser.pugx.org/sokil/user-bundle/v/stable.png)](https://packagist.org/packages/sokil/user-bundle)
[![Total Downloads](http://img.shields.io/packagist/dt/sokil/user-bundle.svg)](https://packagist.org/packages/sokil/user-bundle)

## Installation

Install bundle through composer:
```
composer.phar require sokil/user-bundle
```

Add bundle to AppKernel:
```php
<?php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new FOS\UserBundle\FOSUserBundle(),
            new Sokil\UserBundle\UserBundle(),
        );
    }
}
```

This bundle depends from FosUserBundle, so add some configuration to fos_user in `./app/config/config.yml`:
```yaml
fos_user:
    # user class
    user_class: Sokil\UserBundle\Entity\User
    # group class
    group:
        group_class: Sokil\UserBundle\Entity\Group
    # db
    db_driver: orm
    # security
    firewall_name: main
    #registration
    registration:
        form:
            type: user_registration_form
            name: user
    # notification
    from_email:
        address:        %from_email_address%
        sender_name:    %from_email_sender_name%
```

Bundle requires deploy so call:
```
npm install
grunt
```

Bundle uses assetic so you need to register it in assetic config:
```yaml
assetic:
    bundles:
        - UserBundle
```
