#!/bin/bash

VERSION=23

docker build -t rbook-php8-${VERSION} .


docker run -p 8080:80 \
	--mount type=bind,src=/apps/rbook-3.0/config.php,dst=/var/www/html/rbook/config.php,ro \
	--mount type=bind,src=/apps/rbook-3.0/img,dst=/var/www/html/rbook/img rbook-php8-${VERSION}
