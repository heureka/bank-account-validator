.PHONY: build tests updateCodes

build:
	composer install && composer update

updateCodes:
	curl -L https://www.cnb.cz/cs/platebni_styk/ucty_kody_bank/download/kody_bank_CR.csv > cnf/czech-bank-codes.csv

tests:
	php vendor/bin/phpunit --colors tests/