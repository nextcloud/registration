You need a fully working nextcloud/owncloud instance. Check-out this app into the `apps` folder, install the dependencies, and follow the instructions below. The tests will modify your database but should automatically revert the changes in the cleanup stage.

# Dependencies

FreeBSD packages:

```
php70
php70-ctype
php70-curl
php70-dom
php70-filter
php70-gd
php70-hash
php70-json
php70-mbstring
php70-mysqli
php70-openssl
php70-pcntl
php70-pdo
php70-pdo_mysql
php70-pdo_sqlite
php70-phar
php70-posix
php70-session
php70-simplexml
php70-sqlite3
php70-tokenizer
php70-xml
php70-xmlreader
php70-xmlwriter
php70-zip
php70-zlib
```

# Install phpunit 5.7 which is not in FreeBSD Package repository, using composer

```
cd registration
composer install
```

# Run tests manually

```
vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml
```
