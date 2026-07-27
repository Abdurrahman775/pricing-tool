FROM php:8.3-cli-bookworm

# System deps for gd/zip + Node.js (needed for the AI doc-generation build_docx.js step)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
        unzip git curl ca-certificates gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mysqli gd zip mbstring xml dom \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Production-sane PHP config: no error output in responses (errors go to the
# container's stdout log instead), and output buffering so session/header
# calls after any early output don't silently break.
RUN { \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'output_buffering = 4096'; \
    } > /usr/local/etc/php/conf.d/99-app.ini

WORKDIR /app

# Cache PHP deps in their own layer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Cache generator's node deps (build_docx.js, used by AI doc generation)
COPY generator/package.json generator/package-lock.json generator/
RUN npm --prefix generator ci --omit=dev

# Cache frontend's node deps (Tailwind)
COPY frontend/package.json frontend/package-lock.json frontend/
RUN npm --prefix frontend ci

# Full application source, then compile Tailwind CSS with the real config/source
COPY . .
RUN npm --prefix frontend run build

EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} router.php"]
