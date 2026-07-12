# Создание темы

1. Скопируйте `themes/default` в `themes/my-theme`.
2. Скопируйте `public/themes/default` в `public/themes/my-theme`.
3. Измените `theme.json`.
4. Укажите в `.env`:

```env
CMS_THEME=my-theme
```

5. Выполните:

```powershell
php artisan optimize:clear
```

## Правила

- Тема отвечает только за HTML, CSS, JavaScript и изображения.
- SQL-запросы и бизнес-логика внутри темы запрещены.
- Общие данные передаются контроллерами ядра.
- Обновление ядра не должно изменять каталог пользовательской темы.
