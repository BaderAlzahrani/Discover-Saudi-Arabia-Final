# Railway: يثبت امتداد PostgreSQL لـ PDO (حلّ "could not find driver")
FROM php:8.2-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

# Railway يحقن متغير PORT — الخادم المدمج يستمع على نفس المنفذ
EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t .
