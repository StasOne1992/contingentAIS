#!/bin/bash
function ProgressBar {
    let _progress=(${1}*100/${2}*100)/100
    let _done=(${_progress}*4)/10
    let _left=40-$_done
    _fill=$(printf "%${_done}s")
    _empty=$(printf "%${_left}s")
printf "\rProgress : [${_fill// /*}${_empty// /-}] ${_progress}%%"
}

_start=1
_end=100

function CheckPkgInstall() {
REQUIRED_PKG=$1
AUTO_INSTALL=$2
PKG_OK=$(dpkg-query -W --showformat='${Status}\n' $REQUIRED_PKG|grep "install ok installed")
echo Checking for $REQUIRED_PKG: $PKG_OK
if [ "" = "$PKG_OK" ]; then
	if [ $AUTO_INSTALL=true ]; then
		bash -c "sudo apt-get --yes install $REQUIRED_PKG" >> "apt-get-$REQUIRED_PKG.log"
		echo "You can view the package installation log in the file `apt-get-$REQUIRED_PKG.log`"
	else
		read -n1 -p "No package $REQUIRED_PKG. Install $REQUIRED_PKG?[y,n]" installpkg	
		case $installpkg in
		y|Y) bash -c "sudo apt-get --yes install $REQUIRED_PKG" >> "apt-get-$REQUIRED_PKG.log";;
		n|N) printf  "\n$REQUIRED_PKG don't install\n" ;;
		*) echo  "\ndont know\n" ;;
		esac
	fi
fi
}

function DbInstall(){
read -n1 -p "Do you want to use an external database server? [y,n]\n" doit
echo "\n "
case $doit in
  y|Y) echo yes ;;
  n|N) echo no ;;
  *) echo dont know ;;
esac
}

function configureNGINX()
{
read -n1 -p "Do you want to use SSL? [y,n]\n" doit
echo "\n "
case $doit in
  y|Y) echo yes ;;
  n|N) echo no ;;
  *) echo dont know ;;
esac
echo '
server {
    listen 443 ssl;
	listen [::]:443 ssl;
    ssl_certificate /var/www/ssl-home/project/cert.crt;
   	ssl_certificate_key /var/www/ssl-home/project/key.key;
    index index.php;
    server_name arm.vatholm.ru;
    root /var/www/project/public;
    location / {
        try_files $uri /index.php$is_args$args;
    }
location ~ ^/index\.php(/|$) {
        fastcgi_pass php82-service:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        internal;
    }
location ~ \.php$ {
        return 404;
    }
location /.well-known {
        proxy_pass http://arm.vatholm.ru:3000/.well-known;
    }
error_log /var/log/nginx/project_error.log;
    access_log /var/log/nginx/project_access.log;
}

server {
    listen 80;
    index index.php;
    server_name arm.vatholm.ru;
    root /var/www/project/public;
    return 301 https://arm.vatholm.ru$request_uri;
    error_log /var/log/nginx/project_error.log;
    access_log /var/log/nginx/project_access.log;
}'
}

function install()
{
INSTALL_PATH=/opt/WebAppRoot
echo "Welcome to WebApp installer!";
echo "Step 1. Install Software"

read -n1 -p "Do you want to use automatic installation? [y,n]" doit
printf "\n"
case $doit in
  y|Y) AUTO_INSTALL=true ;;
  n|N) AUTO_INSTALL=false ;;
  *)   AUTO_INSTALL=true ;;
esac

CheckPkgInstall docker-ce AUTO_INSTALL
CheckPkgInstall docker-composer AUTO_INSTALL
CheckPkgInstall git AUTO_INSTALL

echo "Step 2. Build Docker Containers"

read -n1 -p "Do you want to use Portainer Standalone? [y,n]" doit
printf "\n"
case $doit in
  y|Y) AUTO_INSTALL=true ;;
  n|N) AUTO_INSTALL=false ;;
  *)   AUTO_INSTALL=true ;;
esac

read -n1 -p "Do you want to use Portainer Agent? [y,n]" doit
printf "\n"
case $doit in
  y|Y) AUTO_INSTALL=true ;;
  n|N) AUTO_INSTALL=false ;;
  *)   AUTO_INSTALL=true ;;
esac

sudo git clone https://github.com/StasOne1992/contingentAIS.git $INSTALL_PATH
bash -c "docker build -t phpfpm-arm-vatholm-ru:latest $INSTALL_PATH/.docker/php/"
bash -c "docker-compose  --project-directory $INSTALL_PATH --env-file $INSTALL_PATH/.env up -d"
}

install