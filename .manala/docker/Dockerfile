# Init system (none|sysv|openrc|systemd)
ARG MANALA_INIT=none
# Provision system (none|ansible)
ARG MANALA_PROVISION=none

##########
# System #
##########

FROM debian:11-slim AS system

LABEL maintainer="Elao <contact@elao.com>"

ARG MANALA_HOST=linux
ARG MANALA_USER_ID=1000
ARG MANALA_GROUP_ID=1000

# Used for checking we're running inside a container
ENV container="docker"
ENV MANALA_HOST=${MANALA_HOST}

RUN \
    # Disable irrelevants apt-key warnings
    export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE="1" \
    # Disable all debian user interaction
    && export DEBIAN_FRONTEND="noninteractive" \
    && apt-get --quiet --allow-releaseinfo-change update \
    && apt-get --quiet --yes -o=Dpkg::Use-Pty=0 --purge --auto-remove dist-upgrade \
    && apt-get --quiet --yes -o=Dpkg::Use-Pty=0 --no-install-recommends --verbose-versions install \
        gnupg \
        ca-certificates \
        sudo \
        curl \
        make \
        socat \
    # User
    && addgroup --gid ${MANALA_GROUP_ID} app \
    && adduser --disabled-password --gecos "" app --uid ${MANALA_USER_ID} --ingroup app \
    # Sudo
    && echo "app ALL=(ALL) NOPASSWD:ALL" \
        > /etc/sudoers.d/app

WORKDIR /srv/app

########
# Init #
########

# None
FROM system AS init-none

CMD ["sleep", "9999999"]

ENTRYPOINT ["/entrypoint.sh"]

# SysV
FROM system AS init-sysv

RUN \
    # Disable irrelevants apt-key warnings
    export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE="1" \
    # Disable all debian user interaction
    && export DEBIAN_FRONTEND="noninteractive" \
    # Work around chroot check to avoid sysvinit restart after install
    && mv /usr/bin/ischroot /usr/bin/ischroot_ \
    && ln -s /bin/true /usr/bin/ischroot \
    && apt-get --quiet --yes -o=Dpkg::Use-Pty=0 --no-install-recommends --verbose-versions install \
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
    # Disable irrelevants apt-key warnings
    export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE="1" \
    # Disable all debian user interaction
    && export DEBIAN_FRONTEND="noninteractive" \
    && rm -f /etc/init.d/hwclock.sh \
    && apt-get --quiet --yes -o=Dpkg::Use-Pty=0 --no-install-recommends --verbose-versions install \
        openrc

STOPSIGNAL SIGINT

CMD ["/sbin/openrc-init"]

ENTRYPOINT ["/entrypoint.sh"]

# Systemd
FROM system AS init-systemd

RUN \
    # Disable irrelevants apt-key warnings
    export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE="1" \
    # Disable all debian user interaction
    && export DEBIAN_FRONTEND="noninteractive" \
    && apt-get --quiet --yes -o=Dpkg::Use-Pty=0 --no-install-recommends --verbose-versions install \
        systemd dbus \
    && systemctl set-default multi-user.target \
    && sed -i 's/#\(ForwardToConsole=\).*$/\1yes/' \
        /etc/systemd/journald.conf \
    && printf "Defaults env_keep += \"container\"\n" \
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

ARG ANSIBLE_CORE_VERSION=2.11.8
ARG MANALA_PROVISION_LIMIT

# Locale
ENV LANG=C.UTF-8

RUN \
    # Disable irrelevants apt-key warnings
    export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE="1" \
    # Disable all debian user interaction
    && export DEBIAN_FRONTEND="noninteractive" \
    && apt-get --quiet --yes -o=Dpkg::Use-Pty=0 --no-install-recommends --verbose-versions install \
        python3 python3-pip \
        python3-apt \
    && pip3 install ansible-core==${ANSIBLE_CORE_VERSION} \
    && mkdir -p /etc/ansible/ \
    && printf "\
[defaults]\n\
force_color = True\n\
gather_subset = all,!hardware\n\
display_skipped_hosts = False\n\
retry_files_enabled = False\n\
stdout_callback = yaml\n\
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