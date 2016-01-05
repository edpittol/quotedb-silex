# Quote DB Silex

A Silex application example.

The app features:

* Register user through form or OAuth (Facebook and Google)
* Quotes listing on homepage
* If logged in, show quotation form on homepage
* Contact form sent by email

## Instalation

```
$ git clone git@github.com:edpittol/quotedb-silex.git
$ cd quotedb-silex
$ composer install
```

### Configure app

Copy the config example file and put yours credentials values.

```
$ cp config/config.example.yml config/config.yml
```

Create database schema

```
php app/console.php orm:schema-tool:create
```

## Usage

```
$ php -S localhost:8080 -t web web/index.php
```

The app will served in http://localhost:8080.

## Issues

Use the [GitHub project issues](https://github.com/edpittol/quotedb-silex/issues) to send bug, dubts, whatever.