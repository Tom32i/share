# Relative root dir ("."|".."|"../.."|…)
_DIR := $(patsubst ./%,%,$(patsubst %/.manala/Makefile,%,./$(filter %.manala/Makefile,$(MAKEFILE_LIST))))
# Is current dir root ? (""|"1")
_ROOT = $(if $(filter .,$(_DIR)),1)
# Relative current dir ("."|"foo"|"foo/bar"|…)
_CURRENT_DIR = $(patsubst ./%,%,.$(patsubst $(realpath $(CURDIR)/$(_DIR))%,%,$(CURDIR)))
# Relative cache dir ("./.manala/.cache"|"../.manala/.cache"|"../../.manala/.cache"|…)
_CACHE_DIR = $(shell dir=$(_DIR)/.manala/.cache ; mkdir -p $${dir} ; echo $${dir})

include $(_DIR)/.manala/make/text.mk
include $(_DIR)/.manala/make/help.mk
include $(_DIR)/.manala/make/os.mk
include $(_DIR)/.manala/make/try.mk
include $(_DIR)/.manala/make/git.mk
include $(_DIR)/.manala/make/semver.mk

###############
# Environment #
###############

include $(_DIR)/.manala/vagrant/make.mk

# Vagrant commands only available *OUTSIDE* vagrant environment
ifndef VAGRANT

export VAGRANT_CWD = $(_DIR)/.manala

HELP += $(call help_section, Environment)

# Setup commands only available *IN* root directory
ifdef _ROOT
HELP += $(call help,setup,               Setup environment)
setup:
	vagrant up --provision
	$(setup)
endif

HELP += $(call help,up,                  Start the environment)
up:
	vagrant up

HELP += $(call help,halt,                Stop the environment)
halt:
	vagrant halt

HELP += $(call help,reload,              Restart the environment)
reload:
	vagrant reload

HELP += $(call help,ssh,                 Connect to the environment via SSH)
ssh: export DIR = /srv/app/$(_CURRENT_DIR)
ssh:
	vagrant ssh

HELP += $(call help,destroy,             Stop and delete environment)
destroy:
	vagrant destroy

HELP += $(call help,provision,           Provision the environment (TAGS|DIFF|VERBOSE))
provision:
	vagrant provision --provision-with system

else

HELP += $(call help_section, Environment)

HELP += $(call help,provision,             Provision the environment (TAGS|DIFF|VERBOSE))
provision:
	$(call log, Install ansible galaxy collection)
	cd $(_DIR)/.manala/ansible \
		&& ansible-galaxy collection install \
			--requirements-file collections/requirements.yaml \
			--force
	$(call log, Run ansible playbook)
	cd $(_DIR)/.manala/ansible \
		&& ansible-playbook system.yaml \
			--inventory-file inventories \
			--limit development \
			--become \
			$(if $(TAGS),--tags $(TAGS)) \
			$(if $(VARS),--extra-vars '$(VARS)') \
			$(if $(VERBOSE),-vvv) \
			$(if $(DIFF),--check --diff)

endif

HELP += $(call help,provision.apt,         Provision the environment - Apt (DIFF|VERBOSE))
provision.apt: export TAGS = apt
provision.apt: provision

HELP += $(call help,provision.files,       Provision the environment - Files (DIFF|VERBOSE))
provision.files: export TAGS = files
provision.files: provision

HELP += $(call help,provision.nginx,       Provision the environment - Nginx (DIFF|VERBOSE))
provision.nginx: export TAGS = nginx
provision.nginx: provision

HELP += $(call help,provision.supervisor,  Provision the environment - Supervisor (DIFF|VERBOSE))
provision.supervisor: export TAGS = supervisor
provision.supervisor: provision

HELP += $(call help,provision.php,         Provision the environment - Php (DIFF|VERBOSE))
provision.php: export TAGS = php
provision.php: provision

HELP += $(call help,provision.certificates,Provision the environment - Certificates (DIFF|VERBOSE))
ifdef VAGRANT
provision.certificates: export TAGS = certificates
provision.certificates: export VARS = certificates_prompt=true
provision.certificates: provision
else
provision.certificates:
	$(call message_warning, Command only available *INSIDE* vagrant environment)
endif

##########
# Docker #
##########

include $(_DIR)/.manala/docker/make.mk

# Docker commands only available *OUTSIDE* docker environment
ifndef DOCKER

HELP += $(call help_section, Docker)

HELP += $(call help,docker,Run docker container)
docker:
	$(call docker_run)

endif

############
# Releases #
############

HELP += $(call help_section, Releases)

HELP += $(call help,release@production,Release in production)
release@production: SHELL := $(or $(DOCKER_SHELL),$(SHELL))
release@production:
	$(call log, Run ansible playbook)
	ansible-playbook $(_DIR)/.manala/ansible/release.yaml \
		--inventory $(_DIR)/.manala/ansible/inventories/release.yaml \
		--limit production

HELP += $(call help,deploy@production,Deploy in production (REF))
deploy@production: SHELL := $(or $(DOCKER_SHELL),$(SHELL))
deploy@production:
	$(call log, Run ansible playbook)
	ansible-playbook $(_DIR)/.manala/ansible/deploy.yaml \
		--inventory $(_DIR)/.manala/ansible/inventories/deploy.yaml \
		--limit production \
		$(if $(REF),--extra-vars '{"deploy_strategy_git_ref": "$(REF)"}')

HELP += $(call help,release@staging,Release in staging)
release@staging: SHELL := $(or $(DOCKER_SHELL),$(SHELL))
release@staging:
	$(call log, Run ansible playbook)
	ansible-playbook $(_DIR)/.manala/ansible/release.yaml \
		--inventory $(_DIR)/.manala/ansible/inventories/release.yaml \
		--limit staging

HELP += $(call help,deploy@staging,Deploy in staging (REF))
deploy@staging: SHELL := $(or $(DOCKER_SHELL),$(SHELL))
deploy@staging:
	$(call log, Run ansible playbook)
	ansible-playbook $(_DIR)/.manala/ansible/deploy.yaml \
		--inventory $(_DIR)/.manala/ansible/inventories/deploy.yaml \
		--limit staging \
		$(if $(REF),--extra-vars '{"deploy_strategy_git_ref": "$(REF)"}')
