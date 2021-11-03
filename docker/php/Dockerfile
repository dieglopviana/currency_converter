FROM php:8.0-fpm

#USER 1000
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/America/Recife /etc/localtime

RUN sh -c "$(wget -O- https://github.com/deluan/zsh-in-docker/releases/download/v1.1.1/zsh-in-docker.sh)" -- \
    -t avit \
    -p git -p ssh-agent \ 
    -p 'https://github.com/zsh-users/zsh-autosuggestions' \
    -p 'https://github.com/zsh-users/zsh-completions' \
    -p 'https://github.com/zdharma/fast-syntax-highlighting'

RUN apt-get install -y \
    apt-utils \
    openssl \
    nano \
    git \
	nodejs \
	npm 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip

RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install sockets
RUN docker-php-ext-install pcntl

RUN apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# ENV REDIS_VERSION 4.0.2

# RUN curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/$REDIS_VERSION.tar.gz \
#     && tar xfz /tmp/redis.tar.gz \
#     && rm -r /tmp/redis.tar.gz \
#     && mkdir -p /usr/src/php/ext \
#     && mv phpredis-* /usr/src/php/ext/redis

# RUN docker-php-ext-install redis

# RUN docker-php-ext-enable redis

RUN apt-get install -y \ 
    # supervisor \
    cron

RUN apt-get install -y zsh
RUN zsh -c "$(curl -fsSL https://raw.githubusercontent.com/robbyrussell/oh-my-zsh/master/tools/install.sh)" ||true

# Add crontab file in the cron directory
RUN printf '* * * * * /usr/local/bin/php -q -f /var/www/html/artisan schedule:run >> /var/log/cron.log 2>&1\n#' >> /root/crontab

# Give execution rights on the cron job
RUN chmod 0644 /root/crontab

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

RUN crontab /root/crontab

# Permissions
RUN usermod -u 1000 www-data 
RUN groupmod -g 1000 www-data
#RUN chown -R www-data:www-data /var/www/html

# CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]