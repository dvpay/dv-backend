#!/bin/bash

export IM=`whoami`

if [ "${IM}" != "root" ];
then
    echo "Run me from root"
    exit;
fi

export DISTRO_VERSION=$(awk -F'"' '/^VERSION_ID=/ {print $2}' /etc/os-release)
export DISTRO_CODENAME=$(awk -F'=' '/^VERSION_CODENAME=/ {print $2}' /etc/os-release)
export DISTRO_CODENAME=''

if [ -f /etc/redhat-release ];
then
    export OS="redhat"
    export GROUP="wheel"
    export DISTRO="RHEL"
    if [[ "${DISTRO_VERSION}" == "8" || "${DISTRO_VERSION}" == "9" ]];
    then
        if grep -i 'CentOS Stream release' /etc/centos-release &>/dev/null;
        then
            export DISTRO_CODENAME='Stream'
        else
            echo "This scripts work with Centos 8,9 Stream or Centos 7"
            exit;
        fi
    fi
else
    echo "This scripts only Centos"
    exit;
fi

export DATE=`date +%Y.%m.%d`
export DAY=`date +%d`
export DAY2=`date +%-d`
export MONTH=`date +%m`
export YEAR=`date +%Y`
export TIME=`date +%H-%M-%S`

if [[ -z "$1" ]]; then
    read -p "Enter a domain for Merchant control panel (example dv.net): " FRONT
else
    export FRONT="${1}"
fi

if [[ -z "$2" ]]; then
    read -p "Enter backend domain (Press Enter to use the default: api.$FRONT):" BACK

    if [[ -z "$BACK" ]]; then
        export BACK="api.$FRONT"
    else
        export BACK
    fi
else
    export BACK="${2}"
fi

if [[ -z "$3" ]]; then
    read -p "Enter pay form domain (Press Enter to use the default: pay.$FRONT):" PAYDOMAIN

    if [[ -z "$PAYDOMAIN" ]]; then
        export PAYDOMAIN="pay.$FRONT"
    else
        export PAYDOMAIN
    fi
else
    export PAYDOMAIN="${3}"
fi
if [[ -z "$4" ]]; then
    read -p "Enter processing domain (Press Enter to use the default: processing.dv.net):" PROCESSING_URL

    if [[ -z "$PROCESSING_URL" ]]; then
        export PROCESSING_URL="processing.dv.net"
    else
        export PROCESSING_URL
    fi
else
    export PAYDOMAIN="${4}"
fi
if [ "${FRONT}" == "" ]; then
    echo "Empty domain for Mechant control panel!"
    exit;
fi

if [ "${FRONT}" == "" ];
then
    echo "Empty frontend domain!"
    exit;
fi

if [ "${BACK}" == "" ];
then
    echo "Empty backend domain!"
    exit;
fi

echo "Frontend domain: ${FRONT}"
echo "Backend domain: ${BACK}"
echo "Pay domain: ${PAYDOMAIN}"
echo "Processing url: ${PROCESSING_URL}"

adduser server

chmod 775 /home/server

chown -R server:server /home/server

setenforce 0
sed -i 's/^SELINUX=.*/SELINUX=disabled/g' /etc/selinux/config

echo "Update system"

yum -y update
yum -y upgrade
yum -y autoremove

yum -y install epel-release

yum -y install htop git wget curl libpng-devel libxml2-devel libpq-devel zip unzip mc net-tools firewalld tar bind-utils sudo iptables-services glibc-all-langpacks expect yum-utils

systemctl stop firewalld
systemctl disable firewalld
systemctl enable iptables

cat > /etc/sysconfig/iptables <<EOF
*filter
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
-A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
-A INPUT -p icmp -j ACCEPT
-A INPUT -i lo -j ACCEPT
-A INPUT -p tcp -m state --state NEW -m tcp --dport 22 -j ACCEPT
-A INPUT -p tcp -m state --state NEW -m tcp --dport 80 -j ACCEPT
-A INPUT -p tcp -m state --state NEW -m tcp --dport 443 -j ACCEPT
-A INPUT -j REJECT --reject-with icmp-host-prohibited
-A FORWARD -j REJECT --reject-with icmp-host-prohibited
COMMIT
EOF

systemctl restart iptables

echo "Install nginx"

if [[ "${DISTRO_VERSION}" == "8" || "${DISTRO_VERSION}" == "9" ]];
then
    yum -y module reset nginx
    yum -y module enable nginx:1.20
fi

yum -y update

yum -y install nginx

systemctl enable nginx

cat > /etc/nginx/nginx.conf <<EOF
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log;
pid /run/nginx.pid;

include /usr/share/nginx/modules/*.conf;

events {
    worker_connections 1024;
}

http {
    log_format  main  '\$remote_addr - \$remote_user [\$time_local] "\$request" '
                      '\$status \$body_bytes_sent "\$http_referer" '
                      '"\$http_user_agent" "\$http_x_forwarded_for"';

    access_log  /var/log/nginx/access.log  main;

    sendfile            on;
    tcp_nopush          on;
    tcp_nodelay         on;
    keepalive_timeout   65;
    types_hash_max_size 4096;

    include             /etc/nginx/mime.types;
    default_type        application/octet-stream;

    include /etc/nginx/conf.d/*.conf;

}
EOF

cat > "/etc/nginx/conf.d/${FRONT}.conf" <<EOF
server {
    listen 80;
    server_name ${FRONT};

    client_max_body_size 128M;

    root        /home/server/frontend/www/dist;
    index       index.html;

    access_log  /var/log/nginx/frontend.access.log;
    error_log   /var/log/nginx/frontend.error.log;

    location / {
            try_files \$uri \$uri/ /index.html?\$args;
    }

    location ~ /\.(ht|svn|git) {
            deny all;
    }

}
EOF

cat > "/etc/nginx/conf.d/${PAYDOMAIN}.conf" <<EOF
server {
    listen 80;
    server_name ${PAYDOMAIN};

    client_max_body_size 128M;

    root        /home/server/frontend/www/dist;
    index       checkout.html;

    access_log  /var/log/nginx/frontend.access.log;
    error_log   /var/log/nginx/frontend.error.log;

    location / {
            try_files \$uri \$uri/ /checkout.html?\$args;
    }

    location ~ /\.(ht|svn|git) {
            deny all;
    }

}
EOF

cat > "/etc/nginx/conf.d/${BACK}.conf" <<EOF
server {
    listen 80;
    server_name ${BACK};

    client_max_body_size 128M;
    add_header 'Access-Control-Allow-Credentials' 'true';
    add_header 'Access-Control-Allow-Headers' '*' always;
    add_header 'Access-Control-Allow-Methods' 'POST, GET, PUT, PATCH, DELETE, OPTIONS';
    add_header 'Access-Control-Allow-Origin' '*' always;

    root        /home/server/backend/www/public;
    index       index.php;

    access_log  /var/log/nginx/backend.access.log;
    error_log   /var/log/nginx/backend.error.log;

    location / {
        if (\$request_method = OPTIONS) {
            add_header Access-Control-Allow-Origin \$http_origin;
            add_header Access-Control-Allow-Methods 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
            add_header Access-Control-Allow-Headers '*' always;
            add_header Content-Type text/plain;
            add_header Content-Length 0;
            return 204;
        }

        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include fastcgi.conf;
        fastcgi_pass unix:/var/run/php82.sock;
    }

    location ~ /\.(ht|svn|git) {
            deny all;
    }

    real_ip_header CF-Connecting-IP;
}
EOF

systemctl restart nginx

echo "Install php"

if [ "${DISTRO_VERSION}" == "8" ];
then
    yum -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
elif [ "${DISTRO_VERSION}" == "9" ];
then
    yum -y install http://rpms.remirepo.net/enterprise/remi-release-9.rpm
elif [ "${DISTRO_VERSION}" == "7" ];
then
    yum -y install https://rpms.remirepo.net/enterprise/remi-release-7.rpm
else
    exit;
fi

yum -y remove php*

if [[ "${DISTRO_VERSION}" == "8" || "${DISTRO_VERSION}" == "9" ]];
then
    yum -y module reset php
    yum -y module enable php:remi-8.2
elif [ "${DISTRO_VERSION}" == "7" ];
then
    yum-config-manager --enable remi-php82
else
    exit;
fi

yum -y update

#yum -y install php82-php php82-php-{cli,fpm,mysqlnd,pdo_mysql,zip,devel,gd,mbstring,curl,xml,pear,bcmath,json,pecl-redis5,exif,pcntl,sockets,gmp}
yum -y install php82-php php82-php-fpm php82-php-cli php82-php-common php82-php-mysqlnd php82-php-gd php82-php-ldap php82-php-odbc php82-php-pdo php82-php-pecl-memcache \
php82-php-pear php82-php-xml php82-php-xmlrpc php82-php-mbstring php82-php-snmp php82-php-soap php82-php-zip php82-php-opcache php82-php-imap php82-php-bcmath php82-php-intl \
php82-php-pecl-redis5 php82-php-gmp php82-php-dom php82-php-rdkafka php82-php-memcached php82-php-pdo_mysql php82-php-gd php82-php-mbstring php82-php-curl php82-php-exif php82-php-gmp \
php82-php-pcntl php82-php-sockets

ln -s /opt/remi/php82/root/usr/bin/php /usr/bin/php

cat > "/etc/opt/remi/php82/php-fpm.d/www.conf" <<EOF
[www]
user = server
group = server
listen = /var/run/php82.sock
listen.acl_users = nginx
listen.allowed_clients = 127.0.0.1
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
slowlog = /var/log/php-fpm/www-slow.log
php_admin_value[error_log] = /var/log/php-fpm/www-error.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 128M
php_value[session.save_handler] = files
php_value[session.save_path]    = /var/lib/php/session
php_value[soap.wsdl_cache_dir]  = /var/lib/php/wsdlcache
php_value[opcache.file_cache]  = /var/lib/php/opcache
EOF

mkdir -p /var/lib/php/session
mkdir -p /var/lib/php/wsdlcache
mkdir -p /var/lib/php/opcache

mkdir /var/log/php-fpm

chown -R server:server /var/log/php-fpm

chown -R server:server /var/lib/php

systemctl enable php82-php-fpm.service

systemctl restart php82-php-fpm.service

ln -s /opt/remi/php82/root/usr/bin/php /bin/php

echo "Install composer"

wget https://getcomposer.org/composer.phar
chmod +x composer.phar
mv composer.phar /usr/local/bin/composer
ln -s /usr/local/bin/composer /bin/composer

echo "Install nodejs"

if [[ "${DISTRO_VERSION}" == "8" || "${DISTRO_VERSION}" == "9" ]];
then
    yum -y module reset nodejs
    yum -y module enable nodejs:16
fi

yum -y update

yum -y install nodejs npm

echo "Install redis"

yum -y install redis

systemctl start redis.service

systemctl enable redis

echo "Install percona server"

systemctl stop mysqld
rm -f /var/log/mysqld.log
rm -rf /var/lib/mysql/*
yum -y install https://repo.percona.com/yum/percona-release-latest.noarch.rpm
percona-release setup -y ps80
yum -y install percona-server-server percona-server-client percona-server-devel percona-toolkit percona-xtrabackup-80
echo "skip-log-bin" >> /etc/my.cnf
echo "log_bin_trust_function_creators = 1" >> /etc/my.cnf

systemctl start mysqld.service
MYSQLINSTALLPASSWORD=`grep 'temporary password' /var/log/mysqld.log | awk '{print $13}'`
echo "MySQL install password: ${MYSQLINSTALLPASSWORD}"
MYSQLPASSWORD="`cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 15 | head -n 1`-`cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 16 | head -n 1`"
echo "MySQL root password: ${MYSQLPASSWORD}"
echo ${MYSQLPASSWORD} > /root/mysql.pass

mysql --user=root --password="${MYSQLINSTALLPASSWORD}" --connect-expired-password mysql -Bse "
ALTER USER 'root'@'localhost' IDENTIFIED WITH BY \"${MYSQLPASSWORD}\";
FLUSH PRIVILEGES;"
mysql --user=root --password="${MYSQLPASSWORD}" mysql -Bse "
UPDATE user SET host='%' WHERE user='root';
FLUSH PRIVILEGES;
GRANT SYSTEM_USER ON *.* TO root;
GRANT BACKUP_ADMIN ON *.* TO root;
FLUSH PRIVILEGES;"

mysql --user=root --password="${MYSQLPASSWORD}" mysql -Bse "
DELETE FROM user WHERE user='';
FLUSH PRIVILEGES;"

mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql --user=root --password="${MYSQLPASSWORD}" mysql

expect -c "
set timeout 10
spawn mysql_config_editor set --login-path=local --host=localhost --skip-warn --user=root --password
expect -nocase \"Enter password:\"
send \"${MYSQLPASSWORD}\r\"
expect eof
"

mysql --login-path=local -Bse "CREATE DATABASE merchant_dv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

NEW_USERNAME="merchant_dv"
NEW_PASSWORD=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 15 | head -n 1)-$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 16 | head -n 1)

mysql --user=root --password="${MYSQLPASSWORD}" mysql -Bse "
CREATE USER '${NEW_USERNAME}'@'localhost' IDENTIFIED BY '${NEW_PASSWORD}';
GRANT ALL PRIVILEGES ON merchant_dv.* TO '${NEW_USERNAME}'@'localhost';
FLUSH PRIVILEGES;"

echo "New MySQL user created: ${NEW_USERNAME}:${NEW_PASSWORD}"

systemctl enable mysqld

echo "Copy repositories backend"

mkdir ~/.ssh
touch ~/.ssh/known_hosts

touch ~/.ssh/id_rsa.pub
touch ~/.ssh/id_rs

ssh-keygen -F git.github.com || sudo ssh-keyscan git.github.com >> ~/.ssh/known_hosts

chmod 700 ~/.ssh
chmod 600 ~/.ssh/id_rsa.pub
chmod 600 ~/.ssh/id_rsa

rm -rf /home/server/backend/

mkdir -p /home/server/backend/release/target

eval `ssh-agent`

ssh-add ~/.ssh/id_rsa

git clone -b main https://github.com/RadgRabbi/dv-backend /home/server/backend/release/target

cp /home/server/backend/release/target/.env.example /home/server/backend/release/target/.env

sed -i "s/^APP_URL=.*/APP_URL=https:\/\/${BACK}/g" /home/server/backend/release/target/.env
sed -i "s/^APP_DOMAIN=.*/APP_DOMAIN=${FRONT}/g" /home/server/backend/release/target/.env
sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/g" /home/server/backend/release/target/.env
sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/g" /home/server/backend/release/target/.env
sed -i "s/^DB_PORT=.*/DB_PORT=3306/g" /home/server/backend/release/target/.env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=merchant_dv/g" /home/server/backend/release/target/.env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${NEW_USERNAME}/g" /home/server/backend/release/target/.env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${NEW_PASSWORD}/g" /home/server/backend/release/target/.env
sed -i "s/^PAYMENT_FORM_URL=.*/PAYMENT_FORM_URL=https:\/\/${PAYDOMAIN}\/invoices/g" /home/server/backend/release/target/.env
sed -i "s/^PROCESSING_URL=.*/PROCESSING_URL=https:\/\/$PROCESSING_URL/g" /home/server/backend/release/target/.env

chown -R server:server /home/server/backend/

chmod -R 775 /home/server/backend/release/target/storage/
chmod -R 775 /home/server/backend/release/target/bootstrap

sudo -u server -- sh -c 'cd /home/server/backend/release/target; composer install --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts; php artisan key:generate; php artisan migrate --force; php artisan db:seed; php artisan l5-swagger:generate; php artisan cache:currency:rate; php artisan optimize:clear;'
ln -s /home/server/backend/release/target /home/server/backend/www

echo "Copy repositories frontend"

rm -rf /home/server/frontend/

mkdir -p /home/server/frontend/release/target

git clone -b main https://github.com/dvpay/dv-frontend /home/server/frontend/release/target

cp /home/server/frontend/release/target/.env.example /home/server/frontend/release/target/.env

sed -i "s/^VITE_API_URL=.*/VITE_API_URL=http:\/\/${BACK}/g" /home/server/frontend/release/target/.env

chown -R server:server /home/server

sudo -u server -- sh -c 'cd /home/server/frontend/release/target/; npm install; npm run build;'

ln -s /home/server/frontend/release/target /home/server/frontend/www

echo "Install supervisor"

yum -y install supervisor

cat > /etc/supervisord.d/queue-worker.ini <<EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/server/backend/www/artisan queue:work --queue=default,notifications,monitor --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=server
numprocs=8
redirect_stderr=true
stdout_logfile=/home/server/backend/www/storage/logs/worker.log
stopwaitsecs=3600
EOF

systemctl enable supervisord

systemctl restart supervisord

echo "Enable cron"

echo "* * * * * server cd /home/server/backend/www && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/server_schedule_run
echo "*/10 * * * * server cd /home/server/backend/www && php artisan invoice:webhook:missed >> /dev/null 2>&1" > /etc/cron.d/server_invoice_webhook_missed

systemctl restart crond
sudo -u server -- sh -c 'cd /home/server/backend/www/;  php artisan queue:restart; php artisan processing:init; php artisan register:processing:owner'
echo "End work"
