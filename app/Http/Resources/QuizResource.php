<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            
            // --- PERBAIKAN DISINI ---
            // Hapus 'whenLoaded'. Ambil langsung angkanya.
            // Nilai ini otomatis ada karena Anda pakai withCount('questions') di Controller
            'questions_count' => $this->questions_count ?? 0,

            // Relasi questions lengkap (Opsional, hanya jika diload misal untuk halaman detail/edit)
            'questions' => $this->whenLoaded('questions'),

            'course' => $this->when($this->relationLoaded('course'), function () {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}