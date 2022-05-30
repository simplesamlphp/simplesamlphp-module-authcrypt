# AuthCrypt

This module provides two methods for authentication:

`authcrypt:Hash`
: Username & password authentication with hashed passwords.

`authcrypt:Htpasswd`
: Username & password authentication against an `.htpasswd` file.

## authcrypt:Hash

This is based on `exampleAuth:UserPass`, and adds support for hashed passwords.
Hashes can be generated with the included command line tool `bin/pwgen.sh`.
This tool will interactively ask for a password, a hashing algorithm, and
whether or not you want to use a salt:

```bash
[user@server simplesamlphp]$ bin/pwgen.php
Enter password: hackme

$2y$10$PnFsSEv.lda1Qlw4iMtmB.B.ab5y.aT56stBmo9hdCN.rUywQMChC
```

Now create an authentication source in `config/authsources.php` and use the
resulting string as the password:

```php
'example-hashed' => [
    'authCrypt:Hash',
    'student:$2y$10$PnFsSEv.lda1Qlw4iMtmB.B.ab5y.aT56stBmo9hdCN.rUywQMChC' => [
        'uid' => ['student'],
        'eduPersonAffiliation' => ['member', 'student'],
    ],
],
```

This example creates a user `student` with password `hackme`,
and some attributes.

### Compatibility

The generated hashes can also be used in `config.php` for the
administrative password:

```php
'auth.adminpassword' => '$2y$10$PnFsSEv.lda1Qlw4iMtmB.B.ab5y.aT56stBmo9hdCN.rUywQMChC',
```

Instead of generating hashes, you can also use existing ones from OpenLDAP,
provided that the `userPassword` attribute is stored as MD5, SMD5, SHA, or SSHA.

## authCrypt:Htpasswd

Authenticate users against an [`.htpasswd`](htpasswd) file. It can be used for
example when you migrate a web site from basic HTTP authentication to
SimpleSAMLphp.

[htpasswd]: http://httpd.apache.org/docs/2.2/programs/htpasswd.html

The simple structure of the `.htpasswd` file does not allow for per-user
attributes, but you can define some static attributes for all users.

An example authentication source in `config/authsources.php` could look
like this:

```php
    'htpasswd' => [
        'authcrypt:Htpasswd',
        'htpasswd_file' => '/var/www/foo.edu/legacy_app/.htpasswd',
        'static_attributes' => [
            'eduPersonAffiliation' => ['member', 'employee'],
            'Organization' => ['University of Foo'],
        ],
    ],
```
