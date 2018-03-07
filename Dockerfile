FROM heroku-php-nginx

RUN mkdir app
COPY . /app
WORKDIR /app