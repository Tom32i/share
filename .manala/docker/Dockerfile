# Init system (none|sysv|openrc|systemd)
ARG MANALA_INIT="none"
# Provision system (none|ansible)
ARG MANALA_PROVISION="none"

##########
# System #
##########

FROM debian:12-slim AS system

LABEL org.opencontainers.image.authors="Elao <contact@elao.com>"

ARG DEBIAN_FRONTEND="noninteractive"

ARG MANALA_HOST_OS="linux"
ARG MANALA_HOST_DIR
ARG MANALA_USER_ID="1000"
ARG MANALA_GROUP_ID="1000"

ENV MANALA_HOST_OS="${MANALA_HOST_OS}"
ENV MANALA_HOST_DIR="${MANALA_HOST_DIR}"

# The 'container' environment variable tells systemd that it's running inside a
# Docker container environment.
# It's also internally used for checking we're running inside a container.
ENV container="docker"

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

RUN \
    apt-get --quiet update \
    && apt-get --quiet --yes --purge --autoremove upgrade \
    && apt-get --quiet --yes --no-install-recommends --verbose-versions install \
        ca-certificates \
        curl \
        gnupg \
        make \
        socat \
        sudo \
    # User
    && addgroup --gid ${MANALA_GROUP_ID} app \
    && adduser --home /home/app --shell /bin/bash --uid ${MANALA_USER_ID} --gecos app --ingroup app --disabled-password app \
    && echo "app ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/app

WORKDIR /srv/app

########
# Init #
########

# None
FROM system AS init-none

STOPSIGNAL SIGKILL

CMD ["sleep", "9999999"]

ENTRYPOINT ["/entrypoint.sh"]

# SysV
FROM system AS init-sysv

RUN \
    # Work around chroot check to avoid sysvinit restart after install
    mv /usr/bin/ischroot /usr/bin/ischroot_ \
    && ln -s /bin/true /usr/bin/ischroot \
    && apt-get --quiet update \
    && apt-get --quiet --yes --purge --autoremove upgrade \
    && apt-get --quiet --yes --no-install-recommends --verbose-versions install \
        sysvinit-core \
    && mv /usr/bin/ischroot_ /usr/bin/ischroot \
    # Disable getty
    && sed -i "s/^\([[:digit:]]:\)/#\1/g" /etc/inittab \
    # Shutdown on SIGINT
    && sed -i "s/^\(ca:.*\)/ca:12345:ctrlaltdel:\/sbin\/shutdown -h now/g" /etc/inittab \
    # Don't bring networking down right before halting system
    && sed -i "/^NETDOWN=/s/yes/no/" /etc/default/halt \
    # Useless services
    && insserv --remove \
        # rcS / rc2
        bootlogs mountall-bootclean.sh mountall.sh checkfs.sh checkroot-bootclean.sh checkroot.sh hostname.sh \
        hwclock.sh \
        mount-configfs mountdevsubfs.sh mountkernfs.sh \
        brightness \
        mountnfs-bootclean.sh mountnfs.sh \
        urandom \
        bootmisc.sh \
        # rc2
        rmnologin \
        rc.local \
        # rc0
        sendsigs \
        umountnfs.sh \
        umountfs \
        umountroot \
    # Block systemd replacement
    && printf "\
Package: systemd-sysv\n\
Pin: release *\n\
Pin-Priority: -1\n\
" > /etc/apt/preferences

STOPSIGNAL SIGINT

CMD ["/sbin/init"]

ENTRYPOINT ["/entrypoint.sh"]

# OpenRC
FROM system AS init-openrc

RUN \
    rm -f /etc/init.d/hwclock.sh \
    && apt-get --quiet update \
    && apt-get --quiet --yes --purge --autoremove upgrade \
    && apt-get --quiet --yes --no-install-recommends --verbose-versions install \
        openrc

STOPSIGNAL SIGINT

CMD ["/sbin/openrc-init"]

ENTRYPOINT ["/entrypoint.sh"]

# Systemd
FROM system AS init-systemd

RUN \
    apt-get --quiet update \
    && apt-get --quiet --yes --purge --autoremove upgrade \
    && apt-get --quiet --yes --no-install-recommends --verbose-versions install \
        systemd dbus \
    && systemctl set-default multi-user.target \
    && sed -i 's/#\(ForwardToConsole=\).*$/\1yes/' \
        /etc/systemd/journald.conf \
    && echo "Defaults env_keep += \"container\"" \
        > /etc/sudoers.d/systemd \
    && rm -rf \
        /etc/systemd/system/*.wants/* \
        /lib/systemd/system/multi-user.target.wants/* \
        /lib/systemd/system/local-fs.target.wants/* \
        /lib/systemd/system/sockets.target.wants/*udev* \
        /lib/systemd/system/sockets.target.wants/*initctl* \
        /lib/systemd/system/sysinit.target.wants/systemd-tmpfiles-setup-dev* \
        /lib/systemd/system/systemd-update-utmp*

VOLUME /sys/fs/cgroup

STOPSIGNAL SIGRTMIN+3

CMD ["/lib/systemd/systemd"]

ENTRYPOINT ["/entrypoint.sh"]

#############
# Provision #
#############

# None
FROM init-${MANALA_INIT} AS provision-none

# Ansible
FROM init-${MANALA_INIT} AS provision-ansible

ARG ANSIBLE_VERSION="8.7.0"
ARG MANALA_PROVISION_LIMIT
ARG SYMFONY_IDE

# Locale
ENV LANG="C.UTF-8"
RUN \
    apt-get --quiet update \
    && apt-get --quiet --yes --purge --autoremove upgrade \
    && apt-get --quiet --yes --no-install-recommends --verbose-versions install \
        # As managed node
        python3 \
        python3-apt \
        # As controller node
        pipx \
    && PIPX_HOME="/usr/local/pipx" PIPX_BIN_DIR="/usr/local/bin" \
        pipx install ansible==${ANSIBLE_VERSION} \
            --include-deps --pip-args="--no-cache-dir" --verbose \
    && rm -rf ~/.cache/pip /usr/local/pipx/.cache /usr/local/pipx/logs \
    && install --verbose --mode 0755 --directory /etc/ansible \
    && printf "\
[defaults]\n\
force_color = True\n\
display_skipped_hosts = False\n\
retry_files_enabled = False\n\
callback_result_format = yaml\n\
inject_facts_as_vars = False\n\
check_mode_markers = True\n\
[ssh_connection]\n\
pipelining = True\n\
[privilege_escalation]\n\
become_flags = -E\n\
" > /etc/ansible/ansible.cfg

COPY Makefile      ./.manala/
COPY ansible/      ./.manala/ansible/
COPY certificates/ ./.manala/certificates/

RUN \
    if [ -n "${MANALA_PROVISION_LIMIT}" ]; then \
        su app -s /usr/bin/make -- \
            --silent \
            --directory .manala \
            provision \
            LIMIT=${MANALA_PROVISION_LIMIT} ; \
    fi

#######
# App #
#######

FROM provision-${MANALA_PROVISION} AS app

COPY docker/entrypoint.sh /
