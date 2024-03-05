#!/usr/bin/make
SHELL = /bin/sh
UID := $(shell id -u)
GID := $(shell id -g)
export UID
export GID

up-f:
	docker compose -f docker-compose-dev.yml up --build --remove-orphans

up:
	docker compose -f docker-compose-dev.yml up -d --build --remove-orphans 

down:
	docker compose -f docker-compose-dev.yml down --remove-orphans

shell:
	docker exec -u www-data -it app /bin/sh


app:
	[ -d $(APP_VOLUMES_SRC) ] || mkdir src
	docker compose -f docker-compose-dev.yml build \
	&& docker compose -f docker-compose-dev.yml run app \
	composer create laravel/laravel .

key:
	docker compose -f docker-compose-dev.yml run app \
	php artisan key:generate --ansi

