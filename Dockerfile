FROM php:8.2-fpm

# 1. Cài thư viện hệ thống (Thêm libpng, libjpeg để hỗ trợ GD)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev

# 2. Cài PHP Extensions (QUAN TRỌNG: Cài GD và ZIP)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql zip gd

# 3. Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 4. Copy file định nghĩa thư viện
COPY composer.json composer.lock ./

# 5. Cài thư viện (Bây giờ nó sẽ chạy ngon vì đã có GD và ZIP ở bước 2)
# Thêm cờ --ignore-platform-req=ext-gd để chắc ăn vượt qua lỗi check version
RUN composer install --no-scripts --ignore-platform-req=ext-gd

# 6. Copy code vào (Lúc này .dockerignore sẽ chặn cái vendor rác ở ngoài)
COPY . .

# 7. Phân quyền
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache