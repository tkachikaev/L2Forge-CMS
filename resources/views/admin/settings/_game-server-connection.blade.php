<details class="settings-connection-placeholder">
    <summary>
        <span>
            <strong>Будущее подключение к игровой базе</strong>
            <small>Параметры пока не сохраняются и не используются CMS.</small>
        </span>
        <span class="settings-coming-soon">Скоро</span>
    </summary>

    <div class="settings-disabled-notice">
        Поля подготовлены для будущего подключения L2J Mobius.
    </div>

    <fieldset class="settings-disabled-fields" disabled>
        <div class="form-group">
            <label for="{{ $fieldPrefix }}_database_host">Адрес сервера базы данных</label>
            <input id="{{ $fieldPrefix }}_database_host" type="text" placeholder="127.0.0.1">
        </div>

        <div class="form-group">
            <label for="{{ $fieldPrefix }}_database_port">Порт базы данных</label>
            <input id="{{ $fieldPrefix }}_database_port" type="text" inputmode="numeric" placeholder="3306">
        </div>

        <div class="form-group">
            <label for="{{ $fieldPrefix }}_database_name">Название игровой базы данных</label>
            <input id="{{ $fieldPrefix }}_database_name" type="text">
        </div>

        <div class="form-group">
            <label for="{{ $fieldPrefix }}_database_username">Пользователь игровой базы данных</label>
            <input id="{{ $fieldPrefix }}_database_username" type="text">
        </div>

        <div class="form-group settings-disabled-full">
            <label for="{{ $fieldPrefix }}_database_password">Пароль игровой базы данных</label>
            <input id="{{ $fieldPrefix }}_database_password" type="password">
        </div>
    </fieldset>

    <small class="settings-security-note">
        Способ безопасного хранения будет реализован вместе с подключением адаптера игрового сервера.
    </small>
</details>
