<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (Topic $topic) {
            $topic->slug = static::generateUniqueSlug($topic->name);
        });
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }
}
