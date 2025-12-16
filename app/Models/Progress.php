<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progress';

    // Update fillable sesuai kolom baru
    protected $fillable = [
        'user_id', 
        'course_id', 
        'status', 
        'percentage',
        'is_completed', // Baru
        'material_id',  // Baru
        'quiz_id'       // Baru
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // --- TAMBAHKAN RELASI INI (PENTING) ---
    
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}