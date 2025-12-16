<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'title', 'description', 'cover_image'];

    // Milik siapa kursus ini? (Instruktur)
    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Materi dalam kursus
    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    // Quiz dalam kursus
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // Accessor untuk URL lengkap cover image
    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }
        
        return \Storage::disk('public')->url($this->cover_image);
    }

    // Siapa saja yang mengambil kursus ini?
    public function students()
    {
        // Relasi many-to-many via tabel progress (opsional tapi berguna)
        return $this->hasMany(Progress::class);
    }
}
