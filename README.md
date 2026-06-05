Описание проекта

Стек:
- Laravel 12
- PostgreSQL
- Redis
- Sanctum
- Docker

Запуск:
docker compose up -d

Миграции:
php artisan migrate --seed

Запуск воркера:
php artisan queue:work

Тесты:
php artisan test

Webhook receiver:
...

API описание:
openapi.yaml
