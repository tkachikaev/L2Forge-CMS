@extends('admin.layouts.panel')

@section('title', 'Настройки')
@section('description', 'Основные параметры публичного сайта.')

@section('content')
@include('admin.settings._tabs')

<form class="settings-form" method="POST" action="{{ route('admin.settings.general.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="settings-grid">
        <section class="form-card">
            <h2>Основные данные</h2>

            <div class="form-group">
                <label for="site_name">Название сайта</label>
                <input id="site_name" name="site_name" type="text" maxlength="100" required value="{{ old('site_name', $settings['name']) }}">
                <small>Используется в заголовке браузера, шапке и публичных страницах.</small>
            </div>

            <div class="form-group">
                <label for="site_description">Краткое описание</label>
                <textarea id="site_description" name="site_description" rows="4" maxlength="255">{{ old('site_description', $settings['description']) }}</textarea>
                <small>Короткое описание проекта для главной страницы и метатега description.</small>
            </div>

            <div class="form-group">
                <label for="timezone">Часовой пояс</label>
                <select id="timezone" name="timezone" required>
                    @foreach ($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', $settings['timezone']) === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
                <small>Используется для публикации новостей по расписанию и отображения дат.</small>
            </div>

            <div class="form-group">
                <label for="admin_email">Email администрации</label>
                <input id="admin_email" name="admin_email" type="email" maxlength="255" value="{{ old('admin_email', $settings['admin_email']) }}" placeholder="admin@example.com">
                <small>Публичный контактный адрес. SMTP-логины и пароли здесь не хранятся.</small>
            </div>

            <div class="form-group">
                <label for="footer_text">Текст в подвале</label>
                <input id="footer_text" name="footer_text" type="text" maxlength="255" value="{{ old('footer_text', $settings['footer_text']) }}">
                <small>По умолчанию: © 2026 L2Forge-CMS.</small>
            </div>
        </section>

        <aside class="settings-media-column">
            <section class="form-card">
                <h2>Логотип</h2>
                <div class="settings-image-upload" data-settings-image>
                    <div @class(['settings-image-preview', 'has-image' => $settings['logo_url']]) data-settings-preview>
                        @if ($settings['logo_url'])
                            <img src="{{ $settings['logo_url'] }}" alt="Текущий логотип" data-settings-preview-image>
                        @else
                            <span data-settings-preview-empty>Логотип не загружен</span>
                        @endif
                    </div>

                    <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-settings-file>
                    <button class="button button-secondary" type="button" data-settings-file-select>Выбрать логотип</button>

                    @if ($settings['logo_url'])
                        <label class="settings-remove-row">
                            <input name="remove_logo" type="checkbox" value="1" @checked(old('remove_logo')) data-settings-remove>
                            <span>Удалить текущий логотип</span>
                        </label>
                    @endif

                    <small>JPG, PNG или WebP, до 5 МБ. SVG намеренно запрещён.</small>
                </div>
            </section>

            <section class="form-card">
                <h2>Favicon</h2>
                <div class="settings-image-upload" data-settings-image>
                    <div @class(['settings-image-preview', 'settings-favicon-preview', 'has-image' => $settings['favicon_url']]) data-settings-preview>
                        @if ($settings['favicon_url'])
                            <img src="{{ $settings['favicon_url'] }}" alt="Текущий favicon" data-settings-preview-image>
                        @else
                            <span data-settings-preview-empty>Иконка не загружена</span>
                        @endif
                    </div>

                    <input id="favicon" name="favicon" type="file" accept=".png,.webp,.ico,image/png,image/webp,image/x-icon,image/vnd.microsoft.icon" data-settings-file>
                    <button class="button button-secondary" type="button" data-settings-file-select>Выбрать favicon</button>

                    @if ($settings['favicon_url'])
                        <label class="settings-remove-row">
                            <input name="remove_favicon" type="checkbox" value="1" @checked(old('remove_favicon')) data-settings-remove>
                            <span>Удалить текущий favicon</span>
                        </label>
                    @endif

                    <small>PNG, WebP или ICO, до 1 МБ. Рекомендуемый размер — 512×512.</small>
                </div>
            </section>
        </aside>
    </div>

    <div class="settings-actions">
        <button class="button button-primary" type="submit">Сохранить настройки</button>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/settings.js') }}" defer></script>
@endpush
