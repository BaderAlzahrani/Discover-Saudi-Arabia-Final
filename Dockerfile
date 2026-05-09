# Railway: يثبت امتداد PostgreSQL لـ PDO (حلّ "could not find driver")
FROM php:8.2-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && php -m | grep -i pdo_pgsql >/dev/null \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Cachebust: 2026-05-09T19:40:00Z - harmless change to force Docker layer rebuild on Railway

WORKDIR /app

COPY . .

# Railway يحقن متغير PORT — الخادم المدمج يستمع على نفس المنفذ
EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t .
