# Task Manager API

REST API для управления проектами, задачами, комментариями и webhook-подписками.

## Стек

- PHP 8.4 в Docker-образе, поддержка проекта: PHP `^8.2`
- Laravel 12
- Laravel Sanctum
- PostgreSQL 17
- Redis 7
- Laravel Queue
- Vite и Tailwind CSS

## Быстрый запуск через Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

После запуска:

- API: `http://localhost:8000/api`
- Web: `http://localhost:8000`
- Webhook receiver: `http://localhost:9000`
- PostgreSQL: `localhost:5432`
- Redis: `localhost:6379`

`docker-compose.yml` монтирует `vendor` отдельным Docker volume, поэтому проект может стартовать без локальной папки `vendor`.

## Переменные окружения

Для Docker используются значения из `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=task_manager
DB_USERNAME=laravel
DB_PASSWORD=secret

QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

Если `.env` отсутствует:

```bash
cp .env.example .env
docker compose exec app php artisan key:generate
```

## Миграции и сиды

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Или одной командой:

```bash
docker compose exec app php artisan migrate --seed
```

## Очереди

В Docker очередь запускается отдельным сервисом `queue`:

```bash
docker compose logs -f queue
```

Локально без Docker:

```bash
php artisan queue:work redis --tries=3 --sleep=3
```

## Тесты

```bash
php artisan test
```

В Docker:

```bash
docker compose exec app php artisan test
```

## Основные endpoints

- `GET /api/health` - health check
- `GET /api/ready` - проверка PostgreSQL и Redis
- `GET /api/metrics` - системные метрики и счетчики сущностей
- `POST /api/login` - получение Sanctum Bearer token
- `GET /api/projects` - список доступных проектов
- `POST /api/projects` - создание проекта
- `GET /api/projects/{project}/tasks` - список задач проекта
- `POST /api/projects/{project}/tasks` - создание задачи владельцем проекта
- `GET /api/tasks/{task}/comments` - комментарии задачи
- `POST /api/tasks/{task}/comments` - создание комментария
- `GET /api/projects/{project}/webhooks` - webhook-подписки проекта
- `POST /api/projects/{project}/webhooks` - создание webhook-подписки владельцем проекта

Полное описание API находится в `openapi.yaml`.

## Авторизация

Большинство API endpoints требуют Bearer token:

```http
Authorization: Bearer <token>
```

Правила доступа:

- Владелец проекта может читать, редактировать и удалять проект.
- Участник проекта может читать проект и задачи.
- Только владелец проекта может создавать, редактировать и удалять задачи.
- Только владелец проекта может создавать и удалять webhook-подписки.

## Webhooks

При отправке webhook приложение добавляет заголовки:

- `X-Signature` - HMAC-SHA256 от JSON payload с использованием `secret` подписки.
- `Idempotency-Key` - стабильный ключ для пары webhook и payload.

Попытки доставки пишутся в таблицу `webhook_attempts`.
