FROM php:8.4-apache

# システム依存関係のインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Node.js 22.xのインストール（Vite 7とTailwind CSS v4に必要）
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache mod_rewriteを有効化
RUN a2enmod rewrite

# DocumentRootをpublicに変更
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# AllowOverrideを有効化（.htaccessを使えるようにする）
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 作業ディレクトリ
WORKDIR /var/www/html

# Composerファイルをコピー
COPY composer.json composer.lock ./

# Composer依存関係をインストール
RUN composer install --no-dev --no-scripts --no-autoloader

# アプリケーションファイルをコピー
COPY . .

# Composerのオートローダーを最適化
RUN composer dump-autoload --optimize

# NPM依存関係をインストールしてビルド
RUN npm ci && npm run build

# ストレージとキャッシュのパーミッション設定
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ポート公開
EXPOSE 80

# 起動スクリプト
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
