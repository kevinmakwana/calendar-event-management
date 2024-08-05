FROM nginx:1.25.5-alpine3.19

COPY ./ssl.pem /etc/nginx/ssl.pem
COPY ./key.pem /etc/nginx/key.pem
COPY nginx.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html

COPY . /var/www/html
