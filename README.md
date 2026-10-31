# Учёт молока

Пять цистерн, у каждой вместимость до 300 л. Заливка — целое число литров и всегда целиком в одну цистерну. Сервер выбирает ту, куда порция влезает и где молока меньше всего. При равном объёме берётся цистерна с меньшим номером.

Бэкенд — Yii3, JSON API, база SQLite. Экран — Vue 3 и Vite.

## Первый запуск

В `backend`:

```bash
composer install
APP_ENV=dev ./yii migrate:up --force-yes
```

В `frontend`:

```bash
npm install
```

Файл базы появится сам: `backend/runtime/database.sqlite`.

## Серверы

Два терминала. Сначала бэкенд.

В `backend`:

```bash
APP_ENV=dev ./yii serve
```

В `frontend`:

```bash
npm run dev
```

Остановка — `Ctrl+C` в терминале этого сервера.

| Что | Адрес |
| --- | --- |
| Экран | http://127.0.0.1:5173 |
| API | http://127.0.0.1:8080 |
| Swagger | http://127.0.0.1:8080/swagger |

Страница ходит в API через Vite: запрос на `/api/...` уходит на бэкенд без этого префикса. Бэкенд должен быть уже запущен.

## Тесты

В `backend`:

```bash
./vendor/bin/codecept run Unit
```
