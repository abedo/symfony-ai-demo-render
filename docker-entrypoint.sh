#!/bin/sh
set -e

# Inicjalizacja bazy wektorowej na Neon.tech i indeksowanie bloga przy starcie
echo "==> Przygotowywanie bazy danych i wektorów..."
php bin/console ai:store:setup ai.store.postgres.symfony_blog --force

echo "==> Indeksowanie bloga Symfony..."
php bin/console ai:store:index blog -vv

echo "==> Uruchamianie serwera Apache..."
exec apache2-foreground
