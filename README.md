# Laravel Explorer 🚀
<kbd>
    <img src="https://github.com/mevlutcelik/laravel-explorer/blob/main/ss.png" width="100%" style="border:1px solid black;"/>
</kbd>

<br/><br/>
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mevlutcelik/laravel-explorer.svg?style=flat-square)](https://packagist.org/packages/mevlutcelik/laravel-explorer)
[![Total Downloads](https://img.shields.io/packagist/dt/mevlutcelik/laravel-explorer.svg?style=flat-square)](https://packagist.org/packages/mevlutcelik/laravel-explorer)
[![License](https://img.shields.io/packagist/l/mevlutcelik/laravel-explorer.svg?style=flat-square)](https://packagist.org/packages/mevlutcelik/laravel-explorer)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/mevlutcelik/laravel-explorer.svg?style=flat-square)]()

An advanced, beautiful, and interactive API route explorer and testing workspace for Laravel 12+. Built with Tailwind CSS and Vanilla JS, it provides a Postman/Insomnia-like experience directly within your Laravel application.

![Laravel Explorer Preview](https://via.placeholder.com/1000x500.png?text=Add+Your+Awesome+Screenshot+Here)

## ✨ Features

- **Interactive API Testing:** Send GET, POST, PUT, PATCH, and DELETE requests directly from your browser.
- **Smart Parameter Detection:** Automatically detects URL Path parameters and analyzes your Controller/Closure code via PHP Reflection to find expected Body/Query parameters.
- **Bearer Token Management:** Seamlessly manage `auth:sanctum` or JWT tokens. Tokens are synced across the UI and persisted in `localStorage`.
- **Built-in REPL Console:** A fully functional JavaScript console to interact with HTML responses, complete with command history (Up/Down arrows).
- **Code Snippet Generator:** Instantly generates ready-to-use `cURL` and `Fetch API` scripts for your endpoints.
- **Visual & Raw Output:** Toggle between a beautifully formatted JSON tree, Raw output, or an interactive HTML iFrame preview.
- **Dark & Light Mode:** Matches your system preference or toggles manually without any flickering (FOUC).
- **Secure by Default:** Strictly runs only in the `local` environment out of the box to prevent data leaks in production.

## 📦 Requirements

- PHP 8.2 or higher
- Laravel 12.0 or higher

## 🚀 Installation

You can install the package via composer. Since this is a developer tool, it is highly recommended to install it as a dev dependency:

```bash
composer require mevlutcelik/laravel-explorer --dev
````

## 🛠️ Usage

Once installed, simply start your Laravel development server:

```bash
php artisan serve
```

Then, visit the explorer in your browser:
**`http://localhost:8000/explorer`**

## ⚙️ Configuration (Optional)

Laravel Explorer works perfectly out of the box. However, if you want to change the default route path, environments, or apply custom middleware, you can publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-explorer-config"
```

This will create a `config/laravel-explorer.php` file in your app:

```php
return [
    /*
     * The path where the explorer will be accessible.
     */
    'path' => 'explorer',

    /*
     * The environments where the explorer is allowed to run.
     * For security reasons, this defaults to 'local' only.
     */
    'environments' => ['local'],

    /*
     * Middleware applied to the explorer route.
     */
    'middleware' => ['web'],
];
```

*Note: If you modify the config file, remember to run `php artisan config:clear`.*

## 🔒 Security Vulnerabilities

If you discover a security vulnerability within Laravel Explorer, please send an e-mail to Mevlüt Çelik via [info@mevlutcelik.com](mailto:info@mevlutcelik.com). All security vulnerabilities will be promptly addressed.

## 👨‍💻 Credits

  - [Mevlüt Çelik](https://www.mevlutcelik.com)
  - [All Contributors](https://github.com/mevlutcelik/laravel-explorer/graphs/contributors)

## 📄 License

The MIT License (MIT). Please see [License File](https://github.com/mevlutcelik/laravel-explorer/blob/main/LICENSE) for more information.
