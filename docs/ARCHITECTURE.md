# Архитектура

```text
HTTP-запрос
  → Laravel route
  → Controller
  → CMS database / GameServerAdapter
  → Blade theme
  → HTML response
```

## Разделение ответственности

- `app/` — ядро и бизнес-логика.
- `themes/` — только представления темы и её manifest.
- `public/themes/` — CSS, JavaScript и изображения.
- `database/` — структура собственной базы CMS.
- подключение `game` — база Mobius, не используется миграциями CMS.

## Темы

Активная тема задаётся `CMS_THEME=default`. Тема обязана содержать:

```text
themes/example/
├── theme.json
└── views/
```

Публичные файлы темы:

```text
public/themes/example/assets/
```

Тема не должна выполнять SQL-запросы и изменять данные. Она получает уже подготовленные данные от контроллера.

## Игровые адаптеры

`GameServerAdapter` скрывает различия между сборками. Mobius, aCis и другие сборки будут отдельными классами. Так ядро и темы не зависят от названий таблиц конкретной сборки.
