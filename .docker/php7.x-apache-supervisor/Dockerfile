FROM alpine as ioncube_loader
RUN apk --no-cache add curl \
  	&& curl -o ioncube.tar.gz http://downloads3.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz \
	&& tar -xvzf ioncube.tar.gz

FROM ubuntu:18.04
ARG TIMEZONE

LABEL MAINTAINER frcho <luisfer1g@gmail.com>

ARG DEBIAN_FRONTEND=noninteractive
ARG PHP_VERSION=${PHP_VERSION}
ARG DECODE=${DECODE}

RUN \
    if [ "${PHP_VERSION}" = "7.3" ]; then \
        apt-get update && \
        apt-get install -y software-properties-common && \
        LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php; \   
    fi && \
    apt-get update && \
    apt-get install -y --no-install-recommends --no-install-suggests apt-utils \
    ca-certificates gettext \
    apache2 \
    libapache2-mod-php${PHP_VERSION} \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-imap \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-xsl \
    php${PHP_VERSION}-zip \
    curl \
    wget \
    git \
    zip \
    unzip \
    vim \
    htop \
    supervisor \
    cron && \
    rm -rf /var/lib/apt/lists/*

# memory limit php
COPY "uploads.ini" "/etc/php/${PHP_VERSION}/cli/conf.d/uploads.ini"
COPY "uploads.ini" "/etc/php/${PHP_VERSION}/apache2/conf.d/uploads.ini"

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# |--------------------------------------------------------------------------
# | prestissimo
# |--------------------------------------------------------------------------
# |
# | Installs Prestissimo to improve Composer download performance.
# |

RUN composer global require hirak/prestissimo

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /etc/php/${PHP_VERSION}/apache2/conf.d/tzone.ini
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /etc/php/${PHP_VERSION}/cli/conf.d/tzone.ini
RUN "date"

COPY supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# apache stuff
RUN mkdir -p /var/lock/apache2 /var/run/apache2
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

COPY --from=ioncube_loader /ioncube/ioncube_loader_lin_${PHP_VERSION}.so /usr/local/ioncube/

RUN if [ "${DECODE}" = "ioncube" ]; then \ 
        echo "zend_extension=/usr/local/ioncube/ioncube_loader_lin_${PHP_VERSION}.so" > /etc/php/${PHP_VERSION}/apache2/conf.d/00-ioncube.ini && \
        echo "zend_extension=/usr/local/ioncube/ioncube_loader_lin_${PHP_VERSION}.so" > /etc/php/${PHP_VERSION}/cli/conf.d/00-ioncube.ini; \
    fi

# Configure Apache
RUN rm -rf /var/www/* \
    && a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

ADD vhost.conf /etc/apache2/sites-available/000-default.conf

#Add crontab script
COPY crontab /etc/cron.d/crontab

RUN echo 'alias sf="php bin/console"' >> ~/.bashrc

WORKDIR /var/www/html

EXPOSE 80 443

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

CMD ["supervisord", "-n"]
