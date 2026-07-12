# L2Forge Themes

Public themes are stored separately from L2Forge Core.

```text
themes/<slug>/
├─ theme.json
└─ views/
   ├─ layouts/app.blade.php
   └─ home.blade.php

public/themes/<slug>/
└─ assets/
```

The administrator interface does not use public themes.

## Manifest

```json
{
  "name": "Theme name",
  "slug": "theme-slug",
  "version": "1.0.0",
  "author": "Author",
  "cms_min": "0.6.0",
  "cms_max": "1.5.0",
  "description": "Theme description",
  "preview": "assets/images/preview.webp"
}
```

Required fields:

- `name`
- `slug`
- `version`
- `author`

Optional fields:

- `cms_min`
- `cms_max`
- `description`
- `preview`

The `slug` must match the directory name and may contain lowercase Latin letters, digits, hyphens, and underscores.

## Activation

Themes are activated in `/admin/themes`. The selected slug is written to the `cms_settings` table. `CMS_THEME` in `.env` is only a fallback.

L2Forge CMS refuses to activate a theme that is invalid or incompatible.


## News templates

A theme may define:

```text
themes/<slug>/views/news/index.blade.php
themes/<slug>/views/news/show.blade.php
```

Available news data includes:

- `$news->title` — title;
- `$news->excerpt` — short description;
- `$news->published_at` — publication date;
- `$news->coverUrl()` — public cover URL or `null`;
- `$news->safeBodyHtml()` — server-sanitized rich HTML.

Render the full body only as trusted output from `safeBodyHtml()`:

```blade
<article class="news-content">{!! $news->safeBodyHtml() !!}</article>
```

Do not render `$news->body` directly. Themes are responsible only for visual styling of the allowed HTML elements and `data-color` / `data-align` attributes.

## Настройки сайта в теме

Начиная с L2Forge CMS 0.7.0 тема может использовать безопасные функции:

```blade
{{ site_name() }}
{{ site_description() }}
{{ site_footer_text() }}
```

Для изображений:

```blade
@if (site_logo_url())
    <img src="{{ site_logo_url() }}" alt="{{ site_name() }}">
@endif

@if (site_favicon_url())
    <link rel="icon" href="{{ site_favicon_url() }}">
@endif
```

Функции возвращают уже нормализованные значения. Тема не должна самостоятельно читать таблицу `cms_settings` или строить путь к загруженным файлам.

Начиная с 0.7.2 список игровых серверов доступен через:

```blade
@foreach (game_servers() as $serverSettings)
    {{ $serverSettings['name'] }}

    @if ($serverSettings['show_chronicle'])
        {{ $serverSettings['chronicle'] }}
    @endif

    @if ($serverSettings['show_rates'])
        {{ $serverSettings['rates'] }}
    @endif

    @if ($serverSettings['show_mode'])
        {{ $serverSettings['mode'] }}
    @endif
@endforeach
```

Ключи `show_chronicle`, `show_rates` и `show_mode` уже учитывают пустые значения. `show_mode` также скрывает специальное значение `None`. Функция `game_server_settings()` сохранена как сокращение для получения первого сервера и может вернуть `null`, если список пуст.
