<?php
namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Открытие сервера', 'Сервер готовится к открытию. Следите за расписанием и новостями проекта.'],
            ['Стартовые события', 'На старте будут доступны игровые события без платных преимуществ.'],
            ['Работа над CMS', 'Мы создаём открытую, прозрачную и безопасную CMS для сообщества Lineage II.'],
        ] as $i => [$title, $excerpt]) {
            News::updateOrCreate(['slug' => Str::slug($title)], ['title' => $title, 'excerpt' => $excerpt, 'body' => '<p>'.$excerpt.'</p>', 'is_published' => true, 'published_at' => now()->subDays($i)]);
        }
    }
}
