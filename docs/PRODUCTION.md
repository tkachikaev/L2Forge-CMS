# Production-развёртывание

Этот раздел описывает минимальную конфигурацию одного экземпляра L2Forge CMS. Горизонтальное масштабирование и отказоустойчивый кластер требуют отдельной инфраструктуры.

## Обязательные настройки `.env`

Для публичного сайта проверьте как минимум:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
LOG_LEVEL=warning
```

После изменения `.env` очистите кэш конфигурации:

```powershell
php artisan optimize:clear
```

`doctor.ps1` выводит предупреждения, если production-конфигурация оставлена с отладкой, HTTP, небезопасными cookie или уровнем логирования `debug`.

## База CMS и расширения PHP

Для SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Требуется `pdo_sqlite`.

Для MySQL/MariaDB:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=l2forge
DB_USERNAME=l2forge
DB_PASSWORD=change_me
```

Требуется `pdo_mysql`. При `GAME_ADAPTER=mobius` расширение `pdo_mysql` требуется независимо от драйвера основной базы CMS.

## Сессии, кэш и очередь

По умолчанию один экземпляр CMS использует:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Письма сейчас отправляются синхронно. Отдельный queue worker для штатной работы CMS не требуется.

При запуске нескольких экземпляров приложения файловые сессии и кэш использовать нельзя. Для такой схемы нужны общее хранилище сессий и кэша, общая база CMS, единый `APP_KEY`, общее хранилище загрузок и централизованные логи. Redis является рекомендуемым вариантом, но не обязательной зависимостью односерверной установки.

## Laravel Scheduler

Задачи очистки журналов и неиспользуемых изображений запускаются Laravel Scheduler. Команда должна выполняться системой каждую минуту.

### Linux cron

```cron
* * * * * cd /var/www/l2forge && php artisan schedule:run >> /dev/null 2>&1
```

### Windows Task Scheduler

Создайте задачу с повторением каждую минуту.

Программа:

```text
C:\path\to\php.exe
```

Аргументы:

```text
artisan schedule:run
```

Рабочая папка:

```text
C:\Projects\CMS\L2Forge-CMS
```

Однократный ручной запуск `php artisan schedule:run` не создаёт постоянное расписание.

## Резервные копии

Резервное копирование настраивается на уровне сервера или хостинга. Сохраняйте:

- базу CMS;
- `.env` и `APP_KEY`;
- `public/uploads`;
- установленные темы и модули;
- отдельно — игровые базы.

Копия должна храниться вне основного сервера. Периодически проверяйте не только создание архива, но и реальное восстановление.
