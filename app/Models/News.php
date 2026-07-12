<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'image', 'published_at', 'is_published'];
    protected function casts(): array { return ['published_at' => 'datetime', 'is_published' => 'boolean']; }
    public function scopePublished($query) { return $query->where('is_published', true)->where('published_at', '<=', now()); }
}
