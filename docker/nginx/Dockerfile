FROM nginx:1.23

WORKDIR /var/www
COPY ./docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY ./ /var/www/
ENTRYPOINT ["nginx", "-g", "daemon off;"]