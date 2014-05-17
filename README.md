licensify
=========

I needed something simple to add license headers to my projects so I rolled up this Symfony Console based command.
To get started download the phar executable and launch it from terminal.

> You can integrate `LicensifyCommand` into your existing Symfony Console based application by adding `eo/licensify` package in your composer.json. See project page on packagist: https://packagist.org/packages/eo/licensify

## Installation

```
curl -O http://eymengunay.github.io/licensify/downloads/licensify.phar
chmod +x licensify.phar
```

## Usage

```
php licensify.phar --package="Foo" --author="John Doe <john.doe@gmail.com>"
```

## Mini help

```
php licensify.phar -h
```
