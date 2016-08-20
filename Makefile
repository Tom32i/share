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
	composer install --no-progress --no-interaction
	# Npm install
	npm install

install@prod: SYMFONY_ENV = prod
install@prod:
	# Composer
	composer install --prefer-dist --optimize-autoloader --no-progress --no-interaction
	# Symfony cache
	bin/console cache:warmup --no-debug
	# Npm install
	npm --no-spin install

#########
# Build #
#########

## Build application
build:
	gulp watch

build@prod: SYMFONY_ENV = prod
build@prod:
	gulp build

############
# Security #
############

## Run security checks
security:
	security-checker security:check

security@test: SYMFONY_ENV = test
security@test: security

########
# Lint #
########

## Run lint tools
lint:
	php-cs-fixer fix --config-file=.php_cs --dry-run --diff

lint@test: SYMFONY_ENV = test
lint@test: lint

##########
# Deploy #
##########

## Deploy application (demo)
deploy@demo:
	vendor/bin/dep deploy demo

## Deploy application (prod)
deploy@prod:
	vendor/bin/dep deploy prod

## Upload photos (demo)
upload@demo:
	chmod -R 755 web/photos
	rsync -arzv web/photos/* tom32i@deployer.dev:/home/tom32i/family-photos/shared/web/photos #--delete

## Upload photos (prod)
upload@prod:
	chmod -R 755 web/photos
	rsync -arzv web/photos/* tom32i@tom32i.fr:/home/tom32i/family-photos/shared/web/photos #--delete

##########
# Custom #
##########
