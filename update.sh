#!/bin/bash

export IM=`whoami`

if [ "${IM}" != "root" ];
then
    echo "Run me from root"
    exit;
fi

# Configuration
app_dir="/home/server/backend"
repo_url="git@github.com:RadgRabbi/dv-backend.git"
release_dir="/home/server/backend/release"
date=`date '+%Y-%m-%d-%H-%M'`
build_dir=$release_dir/$date

frontend_app_dir="/home/server/frontend"
frontend_repo_url="git@github.com:RadgRabbi/dv-frontend.git"
frontend_release_dir="/home/server/frontend/release"
frontend_build_dir=$frontend_release_dir/$date


if [ ! -f "$app_dir/www/.env" ]; then
  echo "The .env file does not exist in the app directory."
  exit 1
fi
echo "Update backend app"
mkdir -p $build_dir

git clone $repo_url $build_dir

cp $app_dir/www/.env $build_dir/.env
chown -R server:server $app_dir
sudo -u server -- sh -c "cd ${build_dir}; composer install --no-dev; php artisan migrate --seed -n; php artisan l5-swagger:generate -n; php artisan optimize:clear -n"

echo "Update frontend app"

mkdir -p $frontend_build_dir


git clone $frontend_repo_url $frontend_build_dir
cp $frontend_app_dir/www/.env $frontend_build_dir/.env
chown -R server:server $frontend_app_dir

sudo -u server -- sh -c "cd ${frontend_build_dir}; npm install; npm run build;"

rm -rf $app_dir/www
ln -s $build_dir $app_dir/www

rm -rf $frontend_app_dir/www
ln -s $frontend_build_dir $frontend_app_dir/www

systemctl restart php82-php-fpm.service
systemctl restart nginx.service