param(
    [switch]$Fresh,
    [switch]$ClearAllNews,
    [string]$AdminName = 'booogz',
    [string]$AdminEmail = 'booogz@yandex.ru',
    [string]$AdminPassword = 'nWj3LyaeE6jSuE3Iv1dT'
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)][string]$Label,
        [Parameter(Mandatory = $true)][scriptblock]$Command
    )

    Write-Host "==> $Label"
    & $Command

    if ($LASTEXITCODE -ne 0) {
        throw "$Label failed with exit code $LASTEXITCODE."
    }
}

function Write-Utf8NoBom {
    param(
        [Parameter(Mandatory = $true)][string]$Path,
        [Parameter(Mandatory = $true)][string]$Content
    )

    $utf8 = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($Path, $Content, $utf8)
}

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    throw 'PHP was not found in PATH.'
}

if (-not (Test-Path 'vendor\autoload.php')) {
    throw 'vendor\autoload.php was not found. Run .\setup.ps1 first.'
}

if (-not (Test-Path '.env')) {
    throw '.env was not found. Run .\setup.ps1 first.'
}

Invoke-Checked 'Clearing Laravel caches' { php artisan optimize:clear }

if ($Fresh) {
    Invoke-Checked 'Recreating database' { php artisan migrate:fresh --force }
} else {
    Invoke-Checked 'Applying migrations' { php artisan migrate --force }
}

$tempPhpPath = Join-Path $PSScriptRoot '.create-test-news.php'

$tempPhp = @'
<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! Schema::hasTable('admins')) {
    throw new RuntimeException('Table admins does not exist.');
}

if (! Schema::hasTable('news')) {
    throw new RuntimeException('Table news does not exist.');
}

$adminName = trim((string) getenv('TEST_ADMIN_NAME'));
$adminEmail = strtolower(trim((string) getenv('TEST_ADMIN_EMAIL')));
$adminPassword = (string) getenv('TEST_ADMIN_PASSWORD');
$clearAllNews = getenv('TEST_CLEAR_ALL_NEWS') === '1';

$newsItems = [
    [
        'title' => 'Добро пожаловать на сервер L2Forge',
        'slug' => 'test-news-01-welcome',
        'excerpt' => 'Сервер открыт для игроков. Создавайте персонажа и начинайте новое приключение.',
        'body' => '<h2>Добро пожаловать!</h2><p>Мы рады открыть двери нашего сервера для всех поклонников Lineage II.</p><p>Вас ждут стабильная работа, продуманный баланс и регулярные обновления.</p>',
    ],
    [
        'title' => 'Стартовые наборы для новых игроков',
        'slug' => 'test-news-02-starter-kits',
        'excerpt' => 'Что получает каждый новый персонаж после первого входа в игру.',
        'body' => '<h2>Быстрый старт</h2><ul><li>Стартовое оружие и броня</li><li>Заряды души и духа</li><li>Телепорты в основные города</li><li>Временные усиления</li></ul>',
    ],
    [
        'title' => 'Рейты и особенности сервера',
        'slug' => 'test-news-03-server-rates',
        'excerpt' => 'Краткий обзор рейтов, прокачки, добычи и основных настроек.',
        'body' => '<h2>Основные параметры</h2><ul><li><strong>Опыт:</strong> x10</li><li><strong>SP:</strong> x10</li><li><strong>Адена:</strong> x5</li><li><strong>Дроп:</strong> x3</li></ul>',
    ],
    [
        'title' => 'Расписание Олимпиады',
        'slug' => 'test-news-04-olympiad',
        'excerpt' => 'Время проведения боёв и правила определения героев.',
        'body' => '<h2>Олимпиада</h2><p>Бои проводятся ежедневно с 18:00 до 23:00 по времени сервера.</p><p>Период Олимпиады длится две недели.</p>',
    ],
    [
        'title' => 'Клановые войны и репутация',
        'slug' => 'test-news-05-clan-wars',
        'excerpt' => 'Изменения клановой системы и награды за участие в противостояниях.',
        'body' => '<h2>Развитие клана</h2><p>Кланы получают очки репутации за участие в войнах, осадах и победы над рейдовыми боссами.</p>',
    ],
    [
        'title' => 'Возвращение эпических боссов',
        'slug' => 'test-news-06-epic-bosses',
        'excerpt' => 'Обновлено расписание появления Антараса, Валакаса и Баюма.',
        'body' => '<h2>Эпические сражения</h2><ul><li>Антарас — суббота</li><li>Валакас — воскресенье</li><li>Баюм — каждые пять дней</li></ul>',
    ],
    [
        'title' => 'Ивент «Захват флага»',
        'slug' => 'test-news-07-capture-the-flag',
        'excerpt' => 'Командное событие с наградами за победу и активное участие.',
        'body' => '<h2>Capture the Flag</h2><p>Две команды сражаются за флаг противника. Побеждает команда, набравшая больше очков.</p>',
    ],
    [
        'title' => 'Обновление игрового клиента',
        'slug' => 'test-news-08-client-update',
        'excerpt' => 'Перед входом на сервер обновите файлы игры через лаунчер.',
        'body' => '<h2>Новая версия клиента</h2><ol><li>Закройте игру.</li><li>Запустите лаунчер.</li><li>Нажмите кнопку полного обновления.</li></ol>',
    ],
    [
        'title' => 'Как защитить игровой аккаунт',
        'slug' => 'test-news-09-account-security',
        'excerpt' => 'Основные правила безопасности игрового аккаунта.',
        'body' => '<h2>Безопасность</h2><ul><li>Используйте уникальный пароль.</li><li>Не передавайте данные другим игрокам.</li><li>Не запускайте неизвестные программы.</li></ul>',
    ],
    [
        'title' => 'Плановые технические работы',
        'slug' => 'test-news-10-maintenance',
        'excerpt' => 'Сервер будет временно недоступен из-за установки обновления.',
        'body' => '<h2>Технические работы</h2><p>Работы начнутся в 06:00 и займут около одного часа.</p><p>После завершения сервер будет запущен автоматически.</p>',
    ],
    [
        'title' => 'Открыта новая зона охоты',
        'slug' => 'test-news-11-hunting-zone',
        'excerpt' => 'Высокоуровневая локация с усиленными монстрами и ценной добычей.',
        'body' => '<h2>Забытый храм</h2><ul><li>Усиленные монстры</li><li>Редкие материалы</li><li>Групповые задания</li><li>Ежедневный мини-босс</li></ul>',
    ],
    [
        'title' => 'Система достижений',
        'slug' => 'test-news-12-achievements',
        'excerpt' => 'Получайте награды за развитие персонажа, PvP и рейды.',
        'body' => '<h2>Новые цели</h2><p>Добавлены достижения за развитие персонажа, участие в PvP, победы над боссами и исследование мира.</p>',
    ],
    [
        'title' => 'Реферальная программа',
        'slug' => 'test-news-13-referral',
        'excerpt' => 'Приглашайте друзей и получайте бонусы за их развитие.',
        'body' => '<h2>Играть вместе выгоднее</h2><p>Создайте персональную ссылку в личном кабинете и отправьте её друзьям.</p>',
    ],
    [
        'title' => 'Конкурс игровых скриншотов',
        'slug' => 'test-news-14-screenshot-contest',
        'excerpt' => 'Покажите лучшие игровые моменты и получите приз.',
        'body' => '<h2>Условия конкурса</h2><ol><li>Сделайте скриншот.</li><li>Опубликуйте его в сообществе.</li><li>Укажите имя персонажа.</li></ol>',
    ],
    [
        'title' => 'Большой вечерний рейд',
        'slug' => 'test-news-15-evening-raid',
        'excerpt' => 'Общий сбор игроков для похода на рейдовых боссов.',
        'body' => '<h2>Совместный рейд</h2><p>Сбор участников состоится в Гиране. Начало в 20:00 по времени сервера.</p>',
    ],
    [
        'title' => 'Обновление игрового магазина',
        'slug' => 'test-news-16-shop-update',
        'excerpt' => 'В магазин добавлены новые костюмы и декоративные предметы.',
        'body' => '<h2>Новые товары</h2><p>Ассортимент пополнился внешними видами оружия, костюмами и аксессуарами.</p>',
    ],
    [
        'title' => 'Итоги игровой недели',
        'slug' => 'test-news-17-weekly-results',
        'excerpt' => 'Статистика сервера и самые заметные события недели.',
        'body' => '<h2>Неделя в цифрах</h2><ul><li>Создано 420 персонажей</li><li>Проведено 36 клановых войн</li><li>Побеждено 128 рейдовых боссов</li></ul>',
    ],
    [
        'title' => 'Планы развития проекта',
        'slug' => 'test-news-18-roadmap',
        'excerpt' => 'Ближайшие обновления и направления развития сервера.',
        'body' => '<h2>Что будет дальше</h2><ul><li>Расширение личного кабинета</li><li>Новые события</li><li>Улучшение рейтингов</li><li>Дополнительные настройки</li></ul>',
    ],
];

DB::transaction(function () use (
    $adminName,
    $adminEmail,
    $adminPassword,
    $clearAllNews,
    $newsItems
): void {
    $now = now()->format('Y-m-d H:i:s');

    $existingAdmin = DB::table('admins')->where('email', $adminEmail)->first();

    if ($existingAdmin) {
        DB::table('admins')
            ->where('email', $adminEmail)
            ->update([
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'is_active' => true,
                'updated_at' => $now,
            ]);
    } else {
        DB::table('admins')->insert([
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => Hash::make($adminPassword),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    if ($clearAllNews) {
        DB::table('news')->delete();
    } else {
        DB::table('news')
            ->where('slug', 'like', 'test-news-%')
            ->delete();
    }

    foreach ($newsItems as $index => $item) {
        $publishedAt = now()->subDays($index)->setTime(12, 0)->format('Y-m-d H:i:s');

        DB::table('news')->insert([
            'title' => $item['title'],
            'slug' => $item['slug'],
            'excerpt' => $item['excerpt'],
            'body' => $item['body'],
            'image' => null,
            'published_at' => $publishedAt,
            'is_published' => true,
            'created_at' => $publishedAt,
            'updated_at' => $publishedAt,
            'deleted_at' => null,
        ]);
    }
});

$created = DB::table('news')
    ->where('slug', 'like', 'test-news-%')
    ->whereNull('deleted_at')
    ->count();

if ($created !== count($newsItems)) {
    throw new RuntimeException("Expected 18 test news records, created {$created}.");
}

fwrite(STDOUT, "Administrator ready: {$adminEmail}\n");
fwrite(STDOUT, "Test news created: {$created}\n");
'@

Write-Utf8NoBom -Path $tempPhpPath -Content $tempPhp

try {
    $env:TEST_ADMIN_NAME = $AdminName
    $env:TEST_ADMIN_EMAIL = $AdminEmail
    $env:TEST_ADMIN_PASSWORD = $AdminPassword
    $env:TEST_CLEAR_ALL_NEWS = if ($Fresh -or $ClearAllNews) { '1' } else { '0' }

    Invoke-Checked 'Creating administrator and 18 NEWS records' { php $tempPhpPath }
} finally {
    Remove-Item Env:TEST_ADMIN_NAME -ErrorAction SilentlyContinue
    Remove-Item Env:TEST_ADMIN_EMAIL -ErrorAction SilentlyContinue
    Remove-Item Env:TEST_ADMIN_PASSWORD -ErrorAction SilentlyContinue
    Remove-Item Env:TEST_CLEAR_ALL_NEWS -ErrorAction SilentlyContinue
    Remove-Item -LiteralPath $tempPhpPath -Force -ErrorAction SilentlyContinue
}

Invoke-Checked 'Clearing Laravel caches' { php artisan optimize:clear }

Write-Host ''
Write-Host 'Done.' -ForegroundColor Green
Write-Host 'Administrator: booogz@yandex.ru'
Write-Host 'NEWS created: 18'
Write-Host 'The script does not create or modify themes.'
