FROM nginx:stable-alpine AS nginx_builder

COPY ../src/ /var/www/html
COPY ../prod/nginx/default.conf /etc/nginx/conf.d/default.conf