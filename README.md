# SsoServerBundle

Bundle que permite aplicar un servidor para autenticación de usuarios en multiples sitios con un solo login SSO.

## Instalación

Ejecutar: `composer require manuelj555/sso-server-bundle`

Agregar al AppKernel:

```php
public function registerBundles()
{
    $bundles = array(
        ...
        new Ku\SsoClientBundle\KuSsoServerBundle(),
    );

    ...
}
```

Agregar al routing.yml:

```yaml
ku_sso_server:
    resource: "@KuSsoServerBundle/Resources/config/routing.yml"
    prefix:   /sso
```

Luego en el config.yml configurar el bundle:

```yaml
ku_sso_server:
    api_key: debe ser una clave secreta # Clave compartida entre server y cliente para transmisión de datos
    domains: # dominios que representan a los clientes que podrán conectarse usando sso.
        # - http://localhost/
```

Por último se debe añadir la configuración para el firewall en el security.yml:

```yaml
firewalls:
    # ...
    sso_server:
        pattern:  ^/sso/authenticate  # este path va en función  del prefix que se coloque en el routing.yml
        simple_preauth:
            authenticator: ku_sso_server.security.otp_authenticator
```

Con esto la aplicación podrá permitir que los clientes registrados en la clave "domains" se autentiquen contra el servidor.
