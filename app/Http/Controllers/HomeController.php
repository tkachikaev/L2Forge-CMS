<?php
namespace App\Http\Controllers;

use App\Contracts\GameServerAdapter;
use App\Models\News;
use Illuminate\View\View;

final class HomeController
{
    public function __invoke(GameServerAdapter $game): View
    {
        $news = News::query()->published()->latest('published_at')->limit(3)->get();

        return view('theme::home', [
            'news' => $news,
            'server' => array_merge(config('cms.server'), $game->status()),
            'topCharacters' => $game->topCharacters(),
        ]);
    }
}
