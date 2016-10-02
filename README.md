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

Bundle declares some routes, so add them to your `./app.config/routing.yml`:
```yaml
user:
    resource: "@UserBundle/Resources/config/routing.yml"
    prefix:   /
```

Then set access control fot some of them in `./app/config/security.yml`:
```yaml
security:
    # define encoder
    encoders:
        FOS\UserBundle\Model\UserInterface: sha512
    
    # add some roles
    role_hierarchy:
        ROLE_USER_VIEWER: [ROLE_USER]
        ROLE_USER_MANAGER: [ROLE_USER_VIEWER]
        ROLE_ADMIN:
          - ROLE_USER_MANAGER
        ROLE_SUPER_ADMIN:
          - ROLE_ADMIN
          - ROLE_ALLOWED_TO_SWITCH
    
    # define provider
    providers:
        fos_userbundle:
            id: fos_user.user_provider.username_email
            
    # configure filewall
    firewalls:
        # disables authentication for assets and the profiler, adapt it according to your needs
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        # main zone
        main:
            pattern: ^/
            form_login:
                provider: fos_userbundle
                # csrf_provider: form.csrf_provider
                success_handler: user.authentication_success_handler
                failure_handler: user.authentication_failure_handler
            logout:       true
            anonymous:    true
            remember_me:
                key: "%secret%"
                lifetime: 604800 # 1 week
                path: /
                name: token
                httponly: true
                
    # define access control
    access_control:
      - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
      - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
      - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

Create tables:
```
./app/console doctrine:schema:update
```

Create administrator:
```
./app/console fos:user:create admin --super-admin
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

## Singla page application

Bundle builds user interface over (sokil/frontend-bundle)[https://github.com/sokil/FrontendBundle], so initialise in as described in following manual, and add some configuration:

In the spa twig template, add assets and configure app:

```twig
{% import "@UserBundle/Resources/views/macro.html.twig" as userSpa %}

{{ userSpa.jsResources() }}

<script type="text/javascript">
    (function() {
        window.app = new Application(_.extend({
            routers: [
                UserRouter
            ],
        }));
        window.app.start();
    })();
</script>
    
```
