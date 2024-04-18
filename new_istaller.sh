#!/bin/bash

red='\033[0;31m'
green='\033[0;32m'
yellow='\033[0;33m'
plain='\033[0m'

cur_dir=$(pwd)

# check root
[[ $EUID -ne 0 ]] && echo -e "${red}Fatal error: ${plain} Please run this script with root privilege \n " && exit 1

# Check OS and set release variable
if [[ -f /etc/os-release ]]; then
    source /etc/os-release
    release=$ID
elif [[ -f /usr/lib/os-release ]]; then
    source /usr/lib/os-release
    release=$ID
else
    echo "Failed to check the system OS, please contact the author!" >&2
    exit 1
fi
echo "The OS release is: $release"


arch() {
    case "$(uname -m)" in
    x86_64 | x64 | amd64) echo 'amd64' ;;
    i*86 | x86) echo '386' ;;
    armv8* | armv8 | arm64 | aarch64) echo 'arm64' ;;
    armv7* | armv7 | arm) echo 'armv7' ;;
    *) echo -e "${green}Unsupported CPU architecture! ${plain}" && exit 1 ;;
    esac
}
echo "arch: $(arch)"

os_version=""
os_version=$(grep -i version_id /etc/os-release | cut -d \" -f2 | cut -d . -f1)

if [[ "${release}" == "centos" ]]; then
    if [[ ${os_version} -lt 8 ]]; then
        echo -e "${red} Please use CentOS 8 or higher ${plain}\n" && exit 1
    fi
elif [[ "${release}" == "ubuntu" ]]; then
    if [[ ${os_version} -lt 20 ]]; then
        echo -e "${red}please use Ubuntu 20 or higher version! ${plain}\n" && exit 1
    fi

elif [[ "${release}" == "debian" ]]; then
    if [[ ${os_version} -lt 10 ]]; then
        echo -e "${red} Please use Debian 10 or higher ${plain}\n" && exit 1
    fi
else
    echo -e "${red}Failed to check the OS version, please contact the author!${plain}" && exit 1
fi

domain_config() {
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
    if [ "${FRONT}" == "" ]; then
        echo "Empty domain for Mechant control panel!"
        exit;
    fi

    if [ "${FRONT}" == "" ];
    then
        echo "Empty frontend domain!"
        exit;
    fi

    if [ "${FRONT}" == "" ];
    then
        echo "Empty payment form domain!"
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
}

create_user() {
  adduser server
  chmod 775 /home/server
  chown -R server:server /home/server
}

install_dependencies() {
    case "${release}" in
    centos)
        yum -y update && yum -y upgrade && yum -y autoremove && yum -y install epel-release && yum install -y htop git wget curl libpng-devel libxml2-devel libpq-devel zip unzip net-tools tar bind-utils sudo iptables-services glibc-all-langpacks expect yum-utils
        ;;
    *)
        apt -y update && apt install -y -q wget curl tar tzdata
        ;;
    esac
}

enable_iptables() {
    case "${release}" in
      centos)
          iptables_rules /etc/sysconfig/iptables
      ;;
      *)
          iptables_rules /etc/iptables/rules.v4
      ;;
    esac
    systemctl enable iptables
    systemctl restart iptables
}

iptables_rules() {
cat > $1 <<EOF
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
}

install_nginx() {
      echo "Install NGINX"
      case "${release}" in
        centos)
            yum -y module reset nginx && yum -y module enable nginx:1.24 && yum -y update && yum -y install nginx
        ;;
        *)
            apt install -y -q nginx
        ;;
      esac
      systemctl enable nginx
}
config_nginx() {
echo "Config NGINX"
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
}

install_php() {
    echo "Install PHP"
    case "${release}" in
      centos)
          yum -y install https://rpms.remirepo.net/enterprise/remi-release-${DISTRO_VERSION}.rpm
          yum -y remove php*
          yum -y module reset php
          yum -y module enable php:remi-8.2
          yum -y update
          yum -y install php82-php php82-php-fpm php82-php-cli php82-php-common php82-php-mysqlnd php82-php-gd php82-php-ldap php82-php-odbc php82-php-pdo php82-php-pecl-memcache \
          php82-php-pear php82-php-xml php82-php-xmlrpc php82-php-mbstring php82-php-snmp php82-php-soap php82-php-zip php82-php-opcache php82-php-imap php82-php-bcmath php82-php-intl \
          php82-php-pecl-redis5 php82-php-gmp php82-php-dom php82-php-memcached php82-php-pdo_mysql php82-php-gd php82-php-mbstring php82-php-curl php82-php-exif php82-php-gmp \
          php82-php-pcntl php82-php-sockets
          ln -s /opt/remi/php82/root/usr/bin/php /usr/bin/php
      ;;
      *)
          apt -y install php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysqlnd php8.2-gd php8.2-ldap php8.2-odbc php8.2-pdo php8.2-pecl-memcache \
          php8.2-pear php8.2-xml php8.2-xmlrpc php8.2-mbstring php8.2-snmp php82-soap php82-zip php82-opcache php82-imap php82-bcmath php82-intl \
          php82-pecl-redis5 php82-gmp php82-dom php82-memcached php82-pdo_mysql php82-gd php82-mbstring php82-curl php82-exif php82-gmp \
          php82-pcntl php82-sockets
      ;;
    esac
}

config_php() {
      echo "Config PHP"
      case "${release}" in
        centos)
           config_cat "/etc/opt/remi/php82/php-fpm.d/www.conf"
           systemctl enable php82-php-fpm.service
           systemctl restart php82-php-fpm.service
        ;;
        *)
           config_cat "/etc/php/8.2/fpm/pool.d/www.conf"
           systemctl enablep php8.2-fpm.service
           systemctl restart php8.2-fpm.service
        ;;
      esac
      mkdir -p /var/lib/php/session
      mkdir -p /var/lib/php/wsdlcache
      mkdir -p /var/lib/php/opcache
      mkdir /var/log/php-fpm

      chown -R server:server /var/log/php-fpm
      chown -R server:server /var/lib/php
}
config_cat() {
cat > $1 <<EOF
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
}

install_composer() {
  wget https://getcomposer.org/composer.phar
  chmod +x composer.phar
  mv composer.phar /usr/local/bin/composer
}

install_node_js() {
      echo "Install NODE JS"
      case "${release}" in
        centos)
            yum -y install nodejs npm
        ;;
        *)
            apt install -y nodejs npm
        ;;
      esac
}

install_redis() {
      echo "Install NODE JS"
      case "${release}" in
        centos)
            yum -y install redis
        ;;
        *)
            apt install -y redis
        ;;
      esac
      systemctl start redis.service
      systemctl enable redis
}

install_mysql() {
      case "${release}" in
        centos)
            yum -y install https://repo.percona.com/yum/percona-release-latest.noarch.rpm
            percona-release setup -y ps80
            yum -y install percona-server-server percona-server-client percona-server-devel percona-toolkit percona-xtrabackup-80
        ;;
        *)
          curl -O https://repo.percona.com/apt/percona-release_latest.generic_all.deb
          apt install -y gnupg2 lsb-release ./percona-release_latest.generic_all.deb
          apt -y update
          percona-release setup ps80
          apt -y install percona-server-server percona-server-client percona-server-devel percona-toolkit percona-xtrabackup-80
        ;;
      esac
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
}

PG_HOST="127.0.0.1"
PG_USER="processing"
PG_PASS=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 15 | head -n 1)-$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 16 | head -n 1)
PG_NAME="processing_db"

install_postgres() {
      echo "Install POSTGRES"
      case "${release}" in
        centos)
            dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-8-x86_64/pgdg-redhat-repo-latest.noarch.rpm
            dnf -qy module disable postgresql
            dnf install -y postgresql16-server postgresql16 postgresql16-contrib
            sudo /usr/pgsql-16/bin/postgresql-16-setup initdb
            systemctl restart postgresql-16
            systemctl enable postgresql-16
        ;;
        *)
            apt -y install postgresql postgresql-contrib
            systemctl restart postgresql
            systemctl enable postgresql
        ;;
      esac

      sudo -u postgres psql -U postgres -c "CREATE USER $PG_USER WITH PASSWORD '$PG_PASS';"
      sudo -u postgres psql -U postgres -c "CREATE DATABASE $PG_NAME;"
      sudo -u postgres psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE $PG_NAME TO $PG_USER;"
      sudo -u postgres psql -U postgres -d $PG_NAME -c "GRANT ALL ON SCHEMA public TO $PG_USER;"
}
# TODO Remove after public repository
install_processing() {
      echo "INSTALL PROCESSING"
      curl -L   -H "Accept:  application/octet-stream"   -H "Authorization: Bearer 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"  -H "X-GitHub-Api-Version: 2022-11-28"   https://api.github.com/repos/dvpay/processing/releases/assets/162658213 -o processing.zip
      unzip processing.zip /var/app/
      mv /var/app/processing.service /etc/systemd/system/
      mv /var/app/blockparser@.service /etc/systemd/system/
      cp -r /var/app/config.yaml.example /var/app/config.yaml

      GRPC_ADDRESS_BITCOIN="164.138.103.245:8085"
      HTTP_ADDRESS_BITCOIN="164.138.103.245:8091"
      GRPC_ADDRESS_TRON="164.138.103.245:8084"
      HTTP_ADDRESS_TRON="164.138.103.245:8090"

      sed -i "s|host: db|host: $PG_HOST|g" /var/app/config.yaml
      sed -i "s|name: processing|name: $PG_NAME|g" /var/app/config.yaml
      sed -i "s|username: postgres|username: $PG_USER|g" /var/app/config.yaml
      sed -i "s|password: postgres|password: $PG_PASS|g" /var/app/config.yaml

      sed -i "s|grpc_address: explorer-tron:8084|grpc_address: 164.138.103.245:8084|g" /var/app/config.yaml
      sed -i "s|http_address: explorer-tron:8090|http_address: 164.138.103.245:8090|g" /var/app/config.yaml
      sed -i "s|grpc_address: explorer-btc:8085|grpc_address: 164.138.103.245:8085|g" /var/app/config.yaml
      sed -i "s|http_address: explorer-btc:8091|http_address: 164.138.103.245:8091|g" /var/app/config.yaml

      sed -i "s|grpc_address: grpc.shasta.trongrid.io:50051|grpc_address: 80.93.179.250:50051|g" /var/app/config.yaml
      sed -i "s|http_address: https://api.shasta.trongrid.io|http_address: http://80.93.179.250:8090|g" /var/app/config.yaml

      sed -i "s|address: bitcoin-node:8332|address: 80.93.179.252:8332|g" /var/app/config.yaml
      sed -i "s|login: bitcoin-login|login: rpc|g" /var/app/config.yaml
      sed -i "s|secret: bitcoin-password|secret: qh1lFWjT4UPlY0kN|g" /var/app/config.yaml

      sh -c 'cd /var/app/; ./cli db:migrate --certainly'
      systemctl start processing
      systemctl start blockparser@bitcoin.service
      systemctl start blockparser@tron.service
      systemctl enable processing
      systemctl enable blockparser@bitcoin.service
      systemctl enable blockparser@tron.service
}

install_processing