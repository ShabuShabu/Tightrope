<p align="center">
    <img alt="" src="https://github.com/ShabuShabu/Tightrope/blob/develop/tightrope.png"/>
</p>

<h1 align="center">Tightrope</h1>

<p align="center">
    <a href="https://github.com/ShabuShabu/Tightrope/actions?query=workflow%3A%22Run+PHPUnit+tests%22">
        <img alt="PHPUnit Tests" src="https://github.com/ShabuShabu/Tightrope/workflows/Run%20PHPUnit%20tests/badge.svg"/>
    </a>
    <a href="https://github.com/ShabuShabu/Tightrope/blob/develop/LICENSE.md">
        <img alt="GitHub license" src="https://img.shields.io/github/license/ShabuShabu/Tightrope">
    </a>
    <a href="https://github.com/ShabuShabu/Tightrope/tags">
        <img alt="GitHub license" src="https://img.shields.io/github/v/tag/ShabuShabu/Tightrope.svg?sort=semver">
    </a>
</p>


Opinionated authentication helper for [Laravel Passport](https://laravel.com/docs/7.x/passport).

Tightrope will give you all the authentication routes you might need for your API.

## ToDo

- Add tests
- Ensure that events aren't being fired twice
- Pull email verification from BTTs
- Add config descriptions
- Make repo public
- Publish to Packagist
- Journey to the center of Mars

## Installation

You can install the package via composer (:bangbang: at least once it has been published to Packagist...):

```
$ composer require shabushabu/tightrope
```

To make full use of Tightrope, some setup in the `boot` method of your `AppServiceProvider` is required:

```php
Tightrope::routes();

Tightrope::registerUserUsing(function (Request $request) {
    // do your registration thing here, like
    // validation, saving the user, etc
});
```

If you have something special that needs to happen when a user logs out, then use the `Tightrope::logUserOutUsing` method:

```php
Tightrope::logUserOutUsing(function (Request $request) {
    // do something with $request->user()
});
```

## Usage

Nothing more really needs to be done, apart from the above, but there are some areas that can be configured.

### Routes

By default the following non-authenticated routes are registered:

```
POST /register
POST /login
POST /password/request
POST /password/resend
```

Additionally, the following authenticated routes are present:

```
GET  /email/verify/{id}
POST /email/resend
POST /logout
```

The `Tightrope::routes` method does accept a closure that you can use to modify the above routes.
The second argument accepts a options array, that is passed directly to the route group, so you could, for example, add a prefix.

### Requests

Tightrope comes with two form requests, that you can override via the config:

```php
ShabuShabu\Tightrope\Http\Requests\EmailPasswordRequest;
ShabuShabu\Tightrope\Http\Requests\ResetPasswordRequest;
```

### Events

Tightrope fires the following Laravel events:

```php
Illuminate\Auth\Events\PasswordReset;
Illuminate\Auth\Events\Attempting;
Illuminate\Auth\Events\Registered;
Illuminate\Auth\Events\Verified;
Illuminate\Auth\Events\Logout;
Illuminate\Auth\Events\Login;
```

## Testing

Tests will be extracted from the original project in due time.

```
$ composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email boris@shabushabu.eu instead of using the issue tracker.

## :bangbang: Caveats

Tightrope is still young and while it is tested, there will probs be bugs. I will try to iron them out as I find them, but until there's a v1 release, expect things to go :boom:.

## Credits

- [All Contributors](../../contributors)
- [BTT](https://boris.travelled.today), aka **Boris Travelled Today**, where Tightrope was extracted from
- [Ivan Boyko](https://www.iconfinder.com/visualpharm) [[cc]](https://creativecommons.org/licenses/by/3.0/) for the tightrope icon

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
