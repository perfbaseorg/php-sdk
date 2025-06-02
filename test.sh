#!/bin/bash -ex
composer validate --no-check-all --no-check-publish
php -d memory_limit=-1 vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=2G
php -d memory_limit=-1 vendor/bin/phpunit