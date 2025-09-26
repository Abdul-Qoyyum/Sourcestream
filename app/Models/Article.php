<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'source_id',
        'category_id',
        'external_id',
        'title',
        'summary',
        'content',
        'url',
        'image_url',
        'author',
        'published_at',
        'source_metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'source_metadata' => 'array',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
