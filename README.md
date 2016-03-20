# HTTP Basic Auth Guard
[![Latest Version on Packagist][ico-version]][link-packagist] [![Software License][ico-license]](LICENSE.md) [![Total Downloads][ico-downloads]][link-downloads]

> HTTP Basic Auth Guard is a Laravel & Lumen Package that lets you use `basic` as your driver for authentication guard in your application.

> The Guard provides the missing stateless HTTP Basic Authentication possibilities - espacially for Lumen.

## Requirements
- Laravel or Lumen Installation.

## Install
Via Composer

```bash
$ composer require arubacao/http-basic-auth-guard
```

### Read & Follow Documentation
Lumen 5.2: [Authentication](https://lumen.laravel.com/docs/5.2/authentication)

Laravel 5.2: [Authentication](https://laravel.com/docs/5.2/authentication)

**Note:** For Laravel you can use HTTP Basic Auth out-of-the-box with the `session` driver: [Link](https://laravel.com/docs/5.2/authentication#stateless-http-basic-authentication).

 **But the `session` driver does NOT work for Lumen 5.2, so there is no http basic auth out-of-the-box for Lumen 5.2**

### Add the Service Provider
#### Laravel
Open `config/app.php` and, to your `providers` array at the bottom, add:

```php
Arubacao\BasicAuth\BasicGuardServiceProvider::class
```

#### Lumen
Open `bootstrap/app.php` and register the service provider:

```php
$app->register(Arubacao\BasicAuth\BasicGuardServiceProvider::class);
```

## Usage
Open your `config/auth.php` config file and in place of driver under any of your guards, just add the `basic` as your driver and you're all set. Make sure you also set `provider` for the guard to communicate with your database.

**Note:** In Lumen you first have to copy the config file from the directory `vendor/laravel/lumen-framework/config/app.php`, create a `config` folder in your root folder and finally paste the copied file there.

### Setup Guard Driver

```php
// config/auth.php
'guards' => [
    'api' => [
        'driver' => 'basic',
        'provider' => 'users'
    ],

    // ...
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\User::class,
    ],
],
```

### Middleware Usage
Middleware protecting the route:

```php
Route::get('api/whatever', ['middleware' => 'auth:api', 'uses' => 'NiceController@awesome']);
```

Middleware protecting the controller:

```php
<?php

namespace App\Http\Controllers;

class NiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
}
```

## Change log
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Any issues, feedback, suggestions or questions please use issue tracker [here](https://github.com/arubacao/http-basic-auth-guard/issues).

## Security
If you discover any security related issues, please email arubacao@gmail.com instead of using the issue tracker.

## License
The MIT License (MIT).

[ico-version]: https://img.shields.io/packagist/v/arubacao/http-basic-auth-guard.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/arubacao/http-basic-auth-guard/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/arubacao/http-basic-auth-guard.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/arubacao/http-basic-auth-guard.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/arubacao/http-basic-auth-guard.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/arubacao/http-basic-auth-guard
[link-travis]: https://travis-ci.org/arubacao/http-basic-auth-guard
[link-scrutinizer]: https://scrutinizer-ci.com/g/arubacao/http-basic-auth-guard/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/arubacao/http-basic-auth-guard
[link-downloads]: https://packagist.org/packages/arubacao/http-basic-auth-guard
[link-author]: https://github.com/arubacao
[link-contributors]: ../../contributors
