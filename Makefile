.PHONY: build tests

build:
	composer install && composer update

tests:
	php vendor/bin/phpunit --colors tests/