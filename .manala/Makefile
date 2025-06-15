# Project root dir, relative to current dir (.|..|../..|…)
MANALA_DIR := $(patsubst %/.,%,$(patsubst %.manala/..,%.,$(patsubst %Makefile,%..,$(lastword $(MAKEFILE_LIST)))))
# Current dir, relative to project root dir (.|dir|dir/dir|…)
MANALA_CURRENT_DIR = $(patsubst ./%,%,.$(patsubst $(realpath $(CURDIR)/$(MANALA_DIR))%,%,$(CURDIR)))
# Is current dir root ? (|1)
MANALA_CURRENT_ROOT = $(if $(filter .,$(MANALA_CURRENT_DIR)),1)

###########
# Project #
###########

define manala_project_host
share.ela.ooo$(if $(1),:22$(shell printf "%02d" $(1)))
endef

-include $(MANALA_DIR)/.manala/make/text.mk
-include $(MANALA_DIR)/.manala/make/help.mk
-include $(MANALA_DIR)/.manala/make/os.mk
-include $(MANALA_DIR)/.manala/docker/docker.mk
-include $(MANALA_DIR)/.manala/docker/mutagen.mk
-include $(MANALA_DIR)/.manala/ansible/ansible.mk
-include $(MANALA_DIR)/.manala/make/try.mk
-include $(MANALA_DIR)/.manala/make/git.mk
-include $(MANALA_DIR)/.manala/make/semver.mk

##########
# Docker #
##########

MANALA_DOCKER_COMPOSE_ENV += \
	MANALA_HOST_OS=$(MANALA_OS) \
	MANALA_HOST_DIR=$(abspath $(MANALA_DIR))
MANALA_DOCKER_COMPOSE_FILE += \
	$(MANALA_DIR)/.manala/docker/compose/init.sysv.yaml \
	$(if $(SYMFONY_IDE), $(MANALA_DIR)/.manala/docker/compose/symfony.yaml)
MANALA_DOCKER_COMPOSE_PROFILE ?= development

# Command
MANALA_DOCKER_COMMAND = exec --user app
MANALA_DOCKER_COMMAND_SERVICE = app

MANALA_DOCKER_COMMAND_DEFAULT_DIR = /srv/app/$(MANALA_CURRENT_DIR)

# Services

ifeq ($(MANALA_DOCKER_COMPOSE_PROFILE),development)
MANALA_DOCKER_DEFAULT_SERVICES += mailpit
endif

###############
# Environment #
###############

MANALA_HELP += $(call manala_help_section, Environment)

# Docker commands only available *OUTSIDE* docker environment
ifndef MANALA_DOCKER

# Setup commands only available *IN* root directory
ifdef MANALA_CURRENT_ROOT
MANALA_HELP += $(call manala_help,setup,                 Setup environment (DEBUG))
setup: MANALA_DOCKER_DEBUG = $(if $(DEBUG),1)
setup:
	$(manala_docker_compose) up \
		--build \
		--wait
	$(setup)
	$(MAKE) help
endif

MANALA_HELP += $(call manala_help,up,                    Start the environment)
up:
	$(manala_docker_compose) start
	$(MAKE) help.project

MANALA_HELP += $(call manala_help,halt,                  Stop the environment)
halt:
	$(manala_docker_compose) stop

MANALA_HELP += $(call manala_help,reload,                Restart the environment)
reload:
	$(manala_docker_compose) restart
	$(MAKE) help.project

MANALA_HELP += $(call manala_help,sh,                    Shell to the environment (DIR))
sh: MANALA_DOCKER_COMMAND_DIR ?= $(if $(findstring command line,$(origin DIR)),$(DIR))
sh:
	if [ ! -t 0 ] ; then \
		$(call manala_docker_command, --no-TTY) sh -s ; \
	else \
		$(manala_docker_command) sh -c ' \
			if [ -x "$$(command -v zsh)" ] ; then \
				exec zsh --login ; \
			else \
				exec bash --login ; \
			fi \
		' ; \
	fi

MANALA_HELP += $(call manala_help,destroy,               Stop and delete environment)
destroy:
	$(manala_docker_compose) down \
		--rmi local \
		--volumes \
		--remove-orphans

MANALA_HELP += $(call manala_help,docker,                Arbitrary docker compose commands)
ifeq (docker, $(firstword $(MAKECMDGOALS)))
ARGS := $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))
$(eval $(ARGS):;@true)
docker:
	$(manala_docker_compose) $(ARGS)
endif

endif

MANALA_HELP += $(call manala_help,provision,             Provision the environment (TAGS|DIFF|VERBOSE|LIMIT|DEBUG))
provision: SHELL := $(or $(MANALA_DOCKER_SHELL),$(SHELL))
provision: MANALA_ANSIBLE_PLAYBOOK_INVENTORY = $(MANALA_DIR)/.manala/ansible/inventories
provision: MANALA_ANSIBLE_PLAYBOOK_BECOME = 1
provision: MANALA_ANSIBLE_PLAYBOOK_TAGS = $(TAGS)
provision: MANALA_ANSIBLE_PLAYBOOK_CHECK = $(if $(DIFF),1)
provision: MANALA_ANSIBLE_PLAYBOOK_DIFF = $(if $(DIFF),1)
provision: MANALA_ANSIBLE_PLAYBOOK_VERBOSE_MORE = $(if $(VERBOSE),1)
provision: MANALA_ANSIBLE_PLAYBOOK_LIMIT = $(if $(LIMIT),$(LIMIT),development)
provision: MANALA_ANSIBLE_DEBUG = $(if $(DEBUG),1)
provision:
	$(call manala_log, Install ansible galaxy collections)
	$(manala_ansible_galaxy_collection_install) \
		$(MANALA_DIR)/.manala/ansible/collections/requirements.yaml
	$(call manala_log, Run ansible playbook)
	$(manala_ansible_playbook) \
		$(MANALA_DIR)/.manala/ansible/system.yaml

MANALA_HELP += $(call manala_help,provision.apt,         Provision the environment - Apt (DIFF|VERBOSE))
provision.apt: TAGS = apt
provision.apt: provision

MANALA_HELP += $(call manala_help,provision.files,       Provision the environment - Files (DIFF|VERBOSE))
provision.files: TAGS = files
provision.files: provision

MANALA_HELP += $(call manala_help,provision.nginx,       Provision the environment - Nginx (DIFF|VERBOSE))
provision.nginx: TAGS = nginx
provision.nginx: provision

MANALA_HELP += $(call manala_help,provision.php,         Provision the environment - Php (DIFF|VERBOSE))
provision.php: TAGS = php
provision.php: provision

MANALA_HELP += $(call manala_help,provision.certificates,Provision the environment - Certificates (DIFF|VERBOSE))
provision.certificates: TAGS = certificates
provision.certificates: MANALA_ANSIBLE_PLAYBOOK_EXTRA_VARS = certificates_prompt=true
provision.certificates: provision

##############
# Deliveries #
##############

MANALA_HELP += $(call manala_help_section, Deliveries)

MANALA_HELP += $(call manala_help,release@production,Release in production (AUTHOR))
release@production: SHELL := $(or $(MANALA_DOCKER_SHELL),$(SHELL))
release@production: MANALA_ANSIBLE_PLAYBOOK_INVENTORY = $(MANALA_DIR)/.manala/ansible/inventories/release.yaml
release@production: MANALA_ANSIBLE_PLAYBOOK_LIMIT = production
release@production: MANALA_ANSIBLE_PLAYBOOK_EXTRA_VARS = $(if $(AUTHOR),"release_author='$(AUTHOR)'")
release@production:
	$(call manala_log, Run ansible playbook)
	$(manala_ansible_playbook) \
		$(MANALA_DIR)/.manala/ansible/release.yaml

MANALA_HELP += $(call manala_help,deploy@production,Deploy in production (REF))
deploy@production: SHELL := $(or $(MANALA_DOCKER_SHELL),$(SHELL))
deploy@production: MANALA_ANSIBLE_PLAYBOOK_INVENTORY = $(MANALA_DIR)/.manala/ansible/inventories/deploy.yaml
deploy@production: MANALA_ANSIBLE_PLAYBOOK_LIMIT = production
deploy@production: MANALA_ANSIBLE_PLAYBOOK_EXTRA_VARS = $(if $(REF),'{"deploy_strategy_git_ref": "$(REF)"}')
deploy@production:
	$(call manala_log, Run ansible playbook)
	$(manala_ansible_playbook) \
		$(MANALA_DIR)/.manala/ansible/deploy.yaml
	$(call manala_message_success, Visit https://share.tom32i.fr)

MANALA_HELP += $(call manala_help,release@staging,Release in staging (AUTHOR))
release@staging: SHELL := $(or $(MANALA_DOCKER_SHELL),$(SHELL))
release@staging: MANALA_ANSIBLE_PLAYBOOK_INVENTORY = $(MANALA_DIR)/.manala/ansible/inventories/release.yaml
release@staging: MANALA_ANSIBLE_PLAYBOOK_LIMIT = staging
release@staging: MANALA_ANSIBLE_PLAYBOOK_EXTRA_VARS = $(if $(AUTHOR),"release_author='$(AUTHOR)'")
release@staging:
	$(call manala_log, Run ansible playbook)
	$(manala_ansible_playbook) \
		$(MANALA_DIR)/.manala/ansible/release.yaml

MANALA_HELP += $(call manala_help,deploy@staging,Deploy in staging (REF))
deploy@staging: SHELL := $(or $(MANALA_DOCKER_SHELL),$(SHELL))
deploy@staging: MANALA_ANSIBLE_PLAYBOOK_INVENTORY = $(MANALA_DIR)/.manala/ansible/inventories/deploy.yaml
deploy@staging: MANALA_ANSIBLE_PLAYBOOK_LIMIT = staging
deploy@staging: MANALA_ANSIBLE_PLAYBOOK_EXTRA_VARS = $(if $(REF),'{"deploy_strategy_git_ref": "$(REF)"}')
deploy@staging:
	$(call manala_log, Run ansible playbook)
	$(manala_ansible_playbook) \
		$(MANALA_DIR)/.manala/ansible/deploy.yaml
	$(call manala_message_success, Visit https://share.deployer.vm)

#######################
# Deliveries Triggers #
#######################

MANALA_HELP += $(call manala_help_section, Deliveries Triggers)

MANALA_HELP += $(call manala_help,trigger-release@production,Trigger release in production)
trigger-release@production:
	gh workflow run release \
		--field tier=production

MANALA_HELP += $(call manala_help,trigger-deploy@production,Trigger deploy in production (REF))
trigger-deploy@production:
	gh workflow run deploy \
		$(if $(REF),--field ref=$(REF)) \
		--field tier=production

MANALA_HELP += $(call manala_help,trigger-release+deploy@production,Trigger release + deploy in production)
trigger-release+deploy@production:
	gh workflow run release \
		--field deploy=true \
		--field tier=production

MANALA_HELP += $(call manala_help,trigger-release@staging,Trigger release in staging)
trigger-release@staging:
	gh workflow run release \
		--field tier=staging

MANALA_HELP += $(call manala_help,trigger-deploy@staging,Trigger deploy in staging (REF))
trigger-deploy@staging:
	gh workflow run deploy \
		$(if $(REF),--field ref=$(REF)) \
		--field tier=staging

MANALA_HELP += $(call manala_help,trigger-release+deploy@staging,Trigger release + deploy in staging)
trigger-release+deploy@staging:
	gh workflow run release \
		--field deploy=true \
		--field tier=staging

########
# Help #
########

MANALA_HELP_PROJECT = $(MANALA_COLOR_COMMENT)┏(°.°)┛┗(°.°)┓$(MANALA_COLOR_RESET) ♪♫ Let's party ♫♪ $(MANALA_COLOR_COMMENT)┗(°.°)┛┏(°.°)┓$(MANALA_COLOR_RESET)\n
MANALA_HELP_PROJECT += $(call manala_help,Http,         http://$(call manala_project_host, 80))
MANALA_HELP_PROJECT += $(call manala_help,Https,        https://$(call manala_project_host, 43))
MANALA_HELP_PROJECT += $(call manala_help,Mailpit,      http://$(call manala_project_host, 25))
