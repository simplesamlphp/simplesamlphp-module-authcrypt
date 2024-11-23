# Authcrypt

![Build Status](https://github.com/simplesamlphp/simplesamlphp-module-authcrypt/actions/workflows/php.yml/badge.svg)
[![Coverage Status](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-authcrypt/branch/master/graph/badge.svg)](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-authcrypt)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-authcrypt/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-authcrypt/?branch=master)
[![Type Coverage](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authcrypt/coverage.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authcrypt)
[![Psalm Level](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authcrypt/level.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-authcrypt)

## Install

Install with composer

```bash
bin/composer require simplesamlphp/simplesamlphp-module-authcrypt
```

## Configuration

Next thing you need to do is to enable the module:

in `config.php`, search for the `module.enable` key and set `authcrypt` to true:

```php
'module.enable' => [ 'authcrypt' => true, … ],
```
