.SILENT:
.PHONY: help

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## Help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

###########
# Install #
###########

## Install application
install:
	# Composer
	bin/composer install --no-progress --no-interaction
	# Npm install
	npm install

install@prod: export SYMFONY_ENV = prod
install@prod:
	# Composer
	composer install --prefer-dist --optimize-autoloader --no-progress --no-interaction
	# Symfony cache
	bin/console cache:warmup --no-debug
	# Npm install
	NODE_ENV=production npm --no-spin install

#######
# Run #
#######

## Run application
run:
	bin/console server:run

#########
# Build #
#########

## Build application
watch:
	npm run watch

build: export NODE_ENV = production
build:
	npm run build

thumbnail:
	bin/console thumbnail:generate

thumbnail@prod: export SYMFONY_ENV = prod
thumbnail@prod:
	bin/console thumbnail:generate

clear-thumbnail:
	bin/console thumbnail:clear

clear-thumbnail@prod: export SYMFONY_ENV = prod
clear-thumbnail@prod:
	bin/console thumbnail:clear

############
# Security #
############

## Run security checks
security:
	security-checker security:check

security@test: export SYMFONY_ENV = test
security@test: security

########
# Lint #
########

## Run lint tools
lint:
	php-cs-fixer fix --config-file=.php_cs --dry-run --diff

lint@test: export SYMFONY_ENV = test
lint@test: lint

##########
# Deploy #
##########

## Deploy application (demo)
deploy@demo: export ENV = prod
deploy@demo:
	vendor/bin/dep deploy deployer.vm

## Deploy application (prod)
deploy@prod: export ENV = prod
deploy@prod:
	vendor/bin/dep deploy tom32i.fr

## Upload photos (demo)
upload@demo:
	chmod -R 755 var/photos
	rsync -arzv --progress var/photos/* tom32i@deployer.vm:/home/tom32i/family-photos/shared/var/photos #--delete
	vendor/bin/dep thumbnail:generate deployer.vm

## Upload photos (prod)
upload@prod:
	chmod -R 755 var/photos
	rsync -arzv --progress var/photos/* tom32i@tom32i.fr:/home/tom32i/family-photos/shared/var/photos #--delete
	vendor/bin/dep thumbnail:generate tom32i.fr

## Download photos (demo)
download@demo:
	rsync -arzv --progress tom32i@deployer.vm:/home/tom32i/family-photos/shared/var/photos/* var/photos
	vendor/bin/dep thumbnail:generate deployer.vm

## Download photos (prod)
download@prod:
	rsync -arzv --progress tom32i@tom32i.fr:/home/tom32i/family-photos/shared/var/photos/* var/photos
	vendor/bin/dep thumbnail:generate tom32i.fr

##########
# Custom #
##########
