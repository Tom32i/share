.SILENT:
.PHONY: build

-include .manala/Makefile

define setup
	$(DOCKER_MAKE) install build
endef

###########
# Install #
###########

## Install application
install:
	# Composer
	composer install --verbose
	# Npm install
	npm install

install@staging: export APP_ENV = prod
install@staging:
	# Composer
	composer install --verbose --no-progress --no-interaction --prefer-dist --optimize-autoloader --no-scripts
	# Npm
	npm install

install@production: export APP_ENV = prod
install@production:
	# Composer
	composer install --verbose --no-progress --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-dev
	# Npm
	npm install

#######
# Run #
#######

## Run application
start:
	symfony server:start --no-tls

## Serve build
serve:
	php -S 0.0.0.0:8001 -t build

#########
# Build #
#########

## Watch assets
watch:
	npx encore dev --watch

## Build application
build:
	npx encore production

build@staging: build
build@production: build

##########
# Warmup #
##########

warmup:
	#bin/console doctrine:database:create --if-not-exists
	# Doctrine migrations
	#bin/console doctrine:migration:migrate --no-interaction
	# Doctrine fixtures
	#bin/console doctrine:fixtures:load --no-interaction

# Note: This task is invoked after a deployment to staging
warmup@staging: export APP_ENV = prod
warmup@staging:
	# Symfony cache
	bin/console cache:warmup --ansi --no-debug
	# Doctrine migrations
	#bin/console doctrine:migrations:migrate --no-debug --no-interaction  --allow-no-migration

# Note: This task is invoked after a deployment to production
warmup@production: export APP_ENV = prod
warmup@production:
	# Symfony cache
	bin/console cache:warmup --ansi --no-debug
	# Doctrine migrations
	#bin/console doctrine:migrations:migrate --no-debug --no-interaction  --allow-no-migration


############
# Security #
############

## Run security checks
security:
	symfony check:security

security@test: export APP_ENV = test
security@test: security

########
# Lint #
########

## Run lint suite
lint: lint-phpcsfixer lint-phpstan lint-twig lint-yaml lint-eslint lint-stylelint

lint-phpcsfixer: export PHP_CS_FIXER_IGNORE_ENV = true
lint-phpcsfixer:
	vendor/bin/php-cs-fixer fix

lint-phpstan:
	vendor/bin/phpstan analyse src

lint-twig:
	bin/console lint:twig templates

lint-yaml:
	bin/console lint:yaml translations config

lint-eslint:
	npx eslint assets/script --ext .js,.json --fix

lint-stylelint:
	npx stylelint 'assets/style/**/*.scss' --fix

##########
# Upload #
##########

## Upload photos (staging)
upload@staging:
	chmod -R 755 var/share
	rsync -arzv --progress var/share/* tom32i@deployer.vm:/home/tom32i/share/shared/var/share #--delete

## Upload photos (production)
upload@production:
	chmod -R 755 var/share
	rsync -arzv --progress var/share/* tom32i@tom32i.fr:/home/tom32i/share/shared/var/share #--delete

## Download photos (staging)
download@staging:
	rsync -arzv --progress tom32i@deployer.vm:/home/tom32i/share/shared/var/share/* var/share

## Download photos (production)
download@production:
	rsync -arzv --progress tom32i@tom32i.fr:/home/tom32i/share/shared/var/share/* var/share

##########
# Custom #
##########

## New share folder
share:
	bin/console app:share

cache-generate:
	bin/console showcase:cache-generate full
	curl localhost:8000

cache-clear:
	bin/console showcase:cache-clear

## Regenerate all caches
cache-regenerate: cache-clear cache-generate

## Normalize all share folders
normalize:
	bin/console showcase:normalize-names
