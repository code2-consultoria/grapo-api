# FROM php:8.1-fpm-alpine3.18
FROM alpine:3.18

ENV CURRENT_PHP_VERSION=8.1.0

ENV ENV="/etc/profile" \
    FRAMEWORK=laravel \
    CAROOT=/home/code2/.mkcert \
    SSL_DOMAIN="code2.localhost" \
    OPCACHE_MODE="normal" \
    PHP_MEMORY_LIMIT=256M \
    XDEBUG_ENABLED=false \
    XDEBUG_IDE_KEY="code2" \
    NR_ENABLED=false \
    NR_APP_NAME="" \
    NR_LICENSE_KEY="" \
    TERM=xterm-256color \
    COLORTERM=truecolor \
    COMPOSER_PROCESS_TIMEOUT=1200 \
    NPM_PACKAGES="/home/code2/.cache/npm-packages" \
    NODE_PATH="/home/code2/.cache/npm-packages/lib/node_modules" \
    MANPATH="/home/code2/.cache/npm-packages/share/man:/usr/share/man" \
    PREFIX='/home/code2/.local' \
    NGINX_HTTP_PORT="8080" \
    NGINX_HTTPS_PORT="8083" \
    NGINX_MODE="dual" \
    NGINX_LISTEN_MODE="dual" \
    NGINX_FPM_BACKEND_HOST="127.0.0.1" \
    NGINX_FPM_BACKEND_PORT="9000"

RUN echo "---> Enabling PHP-Alpine" && \
    apk add --update \
    gettext \
    less \
    ca-certificates \
    tar \
    xz \
    jq \
    wget \
    curl \
    openssh \
    bash \
    fontconfig \
    libxrender \
    libxext \
    imagemagick \
    nano \
    vim \
    git \
    unzip \
    wget \
    make \
    sudo \
    s6 && \
    echo "---> Preparing and Installing PHP" && \
    apk add --update \
    php \
    php-fpm \
    php-bcmath \
    php-bz2 \
    php-calendar \
    php-curl \
    php-ctype \
    php-exif \
    php-fpm \
    php-gd \
    php-gmp \
    php-iconv \
    php-imap \
    php-intl \
    php-mbstring \
    php-mysqli \
    php-mysqlnd \
    php-pdo_mysql \
    php-opcache \
    php-pdo_pgsql \
    php-pgsql \
    php-posix \
    php-soap \
    php-sodium \
    php-sqlite3 \
    php-pdo_sqlite \
    php-xml \
    php-xmlreader \
    php-openssl \
    php-phar \
    php-xsl \
    php-zip \
    php-zlib \
    php-pcntl \
    php-gettext \
    php-cgi \
    php-sockets \
    php-phpdbg

#RUN apk add --update --no-cache --virtual .build-dependencies .phpize-deps-configure linux-headers $PHPIZE_DEPS \
    #&& pecl install apcu redis xdebug \
    #&& docker-php-ext-enable apcu redis xdebug \
    ## && pecl install apcu imagick memcached mongodb redis xdebug \
    ## && docker-php-ext-enable apcu imagick memcached mongodb redis xdebug \
    #&& pecl clear-cache \
    #&& apk del .build-dependencies

#     && \
#    echo "---> Installing static FFMpeg binaries" && \
#    export FFMPEG_PLATFORM=$(echo $TARGETPLATFORM | sed 's/linux\///g') && \
#    wget -O /tmp/ffmpeg.tar.xz "https://www.johnvansickle.com/ffmpeg/old-releases/ffmpeg-4.4-${FFMPEG_PLATFORM}-static.tar.xz" && \
#    tar --directory=/tmp -xvf /tmp/ffmpeg.tar.xz && \
#    mv "/tmp/ffmpeg-4.4-${FFMPEG_PLATFORM}-static/ffmpeg" /bin/ffmpeg && \
#    mv "/tmp/ffmpeg-4.4-${FFMPEG_PLATFORM}-static/ffprobe" /bin/ffprobe && \
#    mv "/tmp/ffmpeg-4.4-${FFMPEG_PLATFORM}-static/qt-faststart" /bin/qt-faststart && \
#    rm -rf /tmp/ffmpeg*

RUN echo "---> Adding the code2 user" && \
    adduser -D -u 1000 code2 && \
    mkdir -p /var/www/app && \
    chown -R code2:code2 /var/www

COPY ./Docker/etc /etc
COPY ./Docker/scripts /scripts

RUN echo "---> Configuring PHP" && \
		echo "code2  ALL = ( ALL ) NOPASSWD: ALL" >> /etc/sudoers && \
    sed -i "/;error_log = .*/c\error_log = /dev/stderr" /etc/php81/php-fpm.conf && \
    sed -i "/;php_admin_value[memory_limit] = .*/c\php_admin_value[memory_limit] = $PHP_MEMORY_LIMIT" /etc/php81/php-fpm.conf && \
    sed -i "/user = .*/c\user = code2" /etc/php81/php-fpm.conf && \
    sed -i "/^group = .*/c\group = code2" /etc/php81/php-fpm.conf && \
    sed -i "/listen.owner = .*/c\listen.owner = code2" /etc/php81/php-fpm.conf && \
    sed -i "/listen.group = .*/c\listen.group = code2" /etc/php81/php-fpm.conf && \
    sed -i "/listen = .*/c\listen = [::]:9000" /etc/php81/php-fpm.conf && \
    sed -i "/;access.log = .*/c\access.log = /dev/stderr" /etc/php81/php-fpm.conf && \
    sed -i "/;decorate_workers_output = .*/c\decorate_workers_output = no" /etc/php81/php-fpm.conf && \
    sed -i "/;clear_env = .*/c\clear_env = no" /etc/php81/php-fpm.conf && \
    sed -i "/;catch_workers_output = .*/c\catch_workers_output = yes" /etc/php81/php-fpm.conf && \
    sed -i "/;log_level = .*/c\log_level = notice" /etc/php81/php-fpm.conf && \
    sed -i "/;log_limit = .*/c\log_limit = 16384" /etc/php81/php-fpm.conf && \
    sed -i "/pid = .*/c\;pid = /run/php/php8-fpm.pid" /etc/php81/php-fpm.conf && \
    sed -i "/;daemonize = .*/c\daemonize = yes" /etc/php81/php-fpm.conf && \
    sed -i "/error_log = .*/c\error_log = /dev/stderr" /etc/php81/php-fpm.conf && \
    sed -i "/;rlimit_files = .*/c\rlimit_files = 8192" /etc/php81/php-fpm.conf && \
    sed -i "/;rlimit_core = .*/c\rlimit_core = unlimited" /etc/php81/php-fpm.conf && \
    sed -i "/post_max_size = .*/c\post_max_size = 1000M" /etc/php81/php-fpm.conf && \
    sed -i "/upload_max_filesize = .*/c\upload_max_filesize = 1000M" /etc/php81/php-fpm.conf && \
    # sed -i "/zend_extension=xdebug/c\;zend_extension=xdebug" /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
		chown -R code2:code2 /home/code2 && \
		chown -R code2:code2 /scripts && \
		chmod +x /scripts/*.sh && \
		rm -rf /tmp/*

USER root

RUN echo "--> Installing Nginx" && \
    apk add --update --no-cache nginx openssl && \
    rm -rf /tmp/* /var/tmp/* /usr/share/doc/* && \
    echo "--> Fixing permissions" && \
    mkdir /var/tmp/nginx && \
    chown -R code2:code2 /var/tmp/nginx && \
    chown -R code2:code2 /var/run/nginx && \
    chown -R code2:code2 /var/log/nginx && \
    chown -R code2:code2 /var/lib/nginx && \
    chown -R code2:code2 /home/code2 && \
    mv /scripts/start.sh /home/start.sh && \
    chown -R code2:code2 /home/start.sh && \
    chmod +x /home/start.sh && \
    wget getcomposer.org/composer.phar && \
    chmod +x composer.phar && \
    mv composer.phar /usr/local/bin/composer

USER code2

# Application directory
WORKDIR "/var/www/app"

# Environment variables
ENV PATH=/scripts:/home/code2/.local/bin:/home/code2/.cache/npm-packages/bin:/home/code2/.yarn/bin:/home/code2/.composer/vendor/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

# Define the entry point that tries to enable newrelic
ENTRYPOINT ["/scripts/entrypoint.sh"]

# Expose webserver port
EXPOSE ${NGINX_HTTP_PORT}
EXPOSE ${NGINX_HTTPS_PORT}
EXPOSE 6001

# Starts a single shell script that puts php-fpm as a daemon and nginx on foreground
CMD ["/home/start.sh"]