# HTTP Basic Auth Guard
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

> HTTP Basic Auth Guard is a Lumen Package that lets you use `basic` as your driver for the authentication guard in your application.

> The Guard brings back the missing stateless HTTP Basic Authentication possibilities for **Lumen >=5.2**.

## Explanation
As of Lumen 5.2 the **session storage** is not included anymore.  
Unfortunately, for calling `Auth::onceBasic()`, `Auth::basic()`, or [alike](https://github.com/illuminate/auth/blob/v5.2.37/Middleware/AuthenticateWithBasicAuth.php#L38) 
you'll need the [`session` driver](https://github.com/laravel/laravel/blob/v5.2.31/config/auth.php#L40) which requires the **session storage**.  
**Therefore HTTP Basic Authentication does not work out-of-the-box anymore for Lumen `>=5.2`.**  
To be honest, I have no idea why Taylor Otwell removed this functionality from Lumen 5.2.  
My best guess is, that he doesn't even know since my issue got [closed instantly](https://github.com/laravel/lumen-framework/issues/388) on github :smiley:   
Luckily, this package brings the usual functionality back! 

## Requirements
- Lumen **`5.2`** or **above** Installation.
- **Note:** For Laravel 5.* or Lumen 5.1 HTTP Basic Auth still works out-of-the-box with the `session` driver: [Link](https://laravel.com/docs/5.2/authentication#stateless-http-basic-authentication).

## Tested with
- Lumen **`5.2`**, **`5.3`**, **`5.4`**, **`5.5`**, **`5.6`**, **`5.7`**
- PHP **`5.6`**, **`7.0`**, **`7.1`**, **`7.2`**, **`7.3`**  

Current master is for Lumen >= `5.7`.
Use version [`^1.0`](https://github.com/arubacao/http-basic-auth-guard/tree/1.0.4) for Lumen <= `5.6`. 

## Installation

### 1. Pull in package

```bash
$ composer require arubacao/http-basic-auth-guard
```

### 2. Read & Follow Official Lumen Documentation for Authentication

[https://lumen.laravel.com/docs/5.2/authentication](https://lumen.laravel.com/docs/5.2/authentication)

*Important*:
> Before using Lumen's authentication features, you should uncomment the call to register the `AuthServiceProvider` service provider in your `bootstrap/app.php` file.  

```php
// bootstrap/app.php

// Uncomment the following line 
 $app->register(App\Providers\AuthServiceProvider::class);
```

> Of course, any routes you wish to authenticate should be assigned the auth middleware, so you should uncomment the call to $app->routeMiddleware() in your bootstrap/app.php file:

```php
// bootstrap/app.php

// Uncomment the following lines
 $app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
 ]);
```

> If you would like to use `Auth::user()` to access the currently authenticated user, you should uncomment the `$app->withFacades()` method in your `bootstrap/app.php` file.

```php
// bootstrap/app.php

// Uncomment the following lines
 $app->withFacades();
 $app->withEloquent();

```

### 3. Add the Service Provider

Open `bootstrap/app.php` and register the service provider:

```php
// bootstrap/app.php

// Add the following line
$app->register(Arubacao\BasicAuth\BasicGuardServiceProvider::class);
```

### 4. Setup Guard Driver

> **Note:** In Lumen you first have to copy the config file from the directory `vendor/laravel/lumen-framework/config/auth.php`, create a `config` folder in your root folder and finally paste the copied file there.

```bash
$ mkdir config
$ cp vendor/laravel/lumen-framework/config/auth.php config/
```

Open your `config/auth.php` config file.  
In `guards` add a new key of your choice (`api` in this example).  
Add `basic` as the driver.  
Make sure you also set `provider` for the guard to communicate with your database.

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

## Usage
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


[![Analytics](https://ga-beacon.appspot.com/UA-77737156-2/readme?pixel)](https://github.com/arubacao/http-basic-auth-guard)
