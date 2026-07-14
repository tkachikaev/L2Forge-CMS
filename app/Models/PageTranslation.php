<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PageTranslation extends Model
{
    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'body',
        'seo_title',
        'seo_description',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
