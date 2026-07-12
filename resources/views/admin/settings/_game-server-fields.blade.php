<div class="settings-server-fields">
    <div class="form-group">
        <label for="{{ $fieldPrefix }}_name">Имя сервера</label>
        <input
            id="{{ $fieldPrefix }}_name"
            name="server_name"
            type="text"
            maxlength="100"
            required
            value="{{ $values['name'] }}"
            placeholder="L2 Eternal x5"
        >
        <small>Название игрового мира, которое увидят посетители сайта.</small>
    </div>

    <div class="form-group">
        <label for="{{ $fieldPrefix }}_rates">Рейты сервера</label>
        <input
            id="{{ $fieldPrefix }}_rates"
            name="server_rates"
            type="text"
            maxlength="100"
            value="{{ $values['rates'] }}"
            placeholder="x5"
        >
        <small>Необязательно. Пустое значение не показывается в теме.</small>
    </div>

    <div class="form-group">
        <label for="{{ $fieldPrefix }}_chronicle">Хроники</label>
        <input
            id="{{ $fieldPrefix }}_chronicle"
            name="server_chronicle"
            type="text"
            maxlength="100"
            value="{{ $values['chronicle'] }}"
            placeholder="High Five"
        >
        <small>Необязательно. В стандартной теме параметр называется «Хроники».</small>
    </div>

    <div class="form-group">
        <label for="{{ $fieldPrefix }}_mode">Режим</label>
        <input
            id="{{ $fieldPrefix }}_mode"
            name="server_mode"
            type="text"
            maxlength="100"
            value="{{ $values['mode'] }}"
            placeholder="PvP, PvE, Craft или None"
        >
        <small>Укажите <strong>None</strong> или оставьте поле пустым, чтобы скрыть режим на сайте.</small>
    </div>
</div>
