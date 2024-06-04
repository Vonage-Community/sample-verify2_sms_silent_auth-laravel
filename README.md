![The Vonage logo](./vonage_logo.png)
<div align="center" style="font-size: xxx-large">X</div>
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Laravel Silent Authentication Application

Requirements:
* PHP 8.2+
* Docker (via. Laravel Sail)
* Yarn
* Composer

## Installation

1. Install PHP dependencies in the terminal with `composer install`
2. Boot up Sail in the terminal with `./vendor/bin/sail up -d`
3. Run migrations in the terminal with `./vendor/bin/sail artisan migrate`
4. Run the database seeder in the terminal with `./vendor/bin/sail artisan db:seed`
5. Install JavaScript dependencies with `./vendor/bin/sail yarn install`
6. Run frontend server with `./vendor/bin/sail yarn run dev`
7. Create a new application instance in the Vonage API Dashboard
8. Download your private key for your new application ID and place it in the root directory of this project.
9. Create a new .env file by copying the example across in the terminal `cp .env.example .env`
10. Add your application ID to the `VONAGE_APPLICATION_ID` environment variable
11. Change your `VONAGE_PRIVATE_KEY_PATH` variable to read like this: `VONAGE_PRIVATE_KEY_PATH="./private.key"`

## Usage

You now have a SuperUser role that can log in with the email `test@test.com` and the password `password`.

To create new users, you can register them through an unguarded route (`localhost/register`). New users will be assigned the `user` role and
can then use the phone number they have entered as their Silent Authentication with SMS one-time password (OTP) as a fallback.

## Coverage

Silent Authentication works within a set list of territories and providers. Please see [this page](https://developer.vonage.com/en/verify/guides/silent-auth-territories?source=verify) for a complete list.
