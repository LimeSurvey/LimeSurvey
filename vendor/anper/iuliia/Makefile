PHP_VERSION ?= 8.5

php:
	docker run --rm -it -w /app -v .:/app php:$(PHP_VERSION)-cli-alpine sh /app/start.sh

php73: PHP_VERSION = 7.3
php73: php
