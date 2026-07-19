#!/bin/sh
set -e

echo "==> Uruchamianie serwera Apache..."
exec apache2-foreground
