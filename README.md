# UserBundle

User attributes, groups and roles management. Backend and SPA.

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
            new Sokil\CommandBusBundle\CommandBusBundle(),
            new Sokil\UserBundle\UserBundle(),
        );
    }
}
```

## Configuration

Bundle declares some routes, so configure them manually or add pre-configured to your `./app.config/routing.yml`:
```yaml
user:
    resource: "@UserBundle/Resources/config/routing.yml"
    prefix:   /
```

Then set access control for some of them in `./app/config/security.yml`:
```yaml
security:
    # define encoder
    encoders:
        Sokil\UserBundle\Entity\User: sha512
    
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
        user_provider_email:
            id: user.user_provider.email
            
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
                provider: user_provider_email
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
Add some bundle configuration to `app/config/config.yaml`:
```yaml
# User
user:
  # add if user allowed to register himself
  registration:
    security:
      firewall: main
      roles:
        - ROLE_CLIENT
```

Create tables:
```
./app/console doctrine:schema:update
```


## User attributes

User attribures based on [EAV model](https://en.wikipedia.org/wiki/Entity%E2%80%93attribute%E2%80%93value_model). Attributes represented by entities, extended from class `UserAttribute`, values represented by entity `UserAttributeValue`. Attributes associayed with users' groups, so user has only attributes, related to it's groups.

## Authentication

There are two event listeners, which overrides response of login request to handle ajax requests:

```yaml
user.authentication_success_handler:
    class: Sokil\UserBundle\EventListener\AuthenticationSuccessHandler
    arguments: ['@security.http_utils', {}]

user.authentication_failure_handler:
    class: Sokil\UserBundle\EventListener\AuthenticationFailureHandler
    arguments: ['@http_kernel', '@security.http_utils', {}, "@logger"]
```

If you want to override authentification logic, configure security firewall to use this listeners in `./app/config/security.yml`:

```yaml
security:
    firewalls:
        main:
            form_login:
                success_handler: user.authentication_success_handler
                failure_handler: user.authentication_failure_handler
```
