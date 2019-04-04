
# EWS-Authentication Adapter

This is an EWS authentication adapter for zendframework/zend-authentication. It may be used to authenticate against a OWA / EWS System. 
It might be useful for you when you have an external application with users from a AD-domain and an LDAP server is not available.

## How to install

For installing you currently need to specify a version of this project as this project is currently not stable.

Simply do: "composer require somecoding/ews-auth:0.0.x" in your project where x ist the latest version number.

## Usage:

```php
$username = 'test-account';
$password = 'test-password';
$options = [
    'server' => 'owa.example.com',
    'domain' => 'example',
];
$auth = new \EwsAuthAdapter\Ews($options, $username, $password);
```
