FROM php:8.1-alpine as build

ARG USR=neputer
ARG BUILD_VER="1.0.0"

WORKDIR /home/$USR/gitlab-ci-cli

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.3.0

# COPY --chown=$USR:$USR composer* ./
COPY composer* ./

RUN composer install

COPY . .

RUN php gitlab-ci app:build gci --build-version=$BUILD_VER

FROM php:8.1-alpine

ARG USR=neputer

RUN apk update && \
    rm -rf /var/cache/apk/* && \
    addgroup -g 1000 -S $USR && adduser -u 1000 -S $USR

USER $USR

WORKDIR /home/$USR/gitlab-ci-cli

ENV PATH=$PATH:/home/$USR/.local/bin

COPY --from=build --chown=$USR /home/$USR/gitlab-ci-cli/builds/gci /home/$USR/.local/bin/

ENTRYPOINT [ "gci" ]
