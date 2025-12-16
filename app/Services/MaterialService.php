<?php

namespace App\Services;

use App\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaterialService
{
    public function createMaterial(array $data, UploadedFile $file): Material
    {
        return DB::transaction(function () use ($data, $file) {
            // Store file in materials directory
            $path = $file->store('materials', 'public');

            // Create material record
            $material = Material::create([
                'course_id' => $data['course_id'],
                'title' => $data['title'],
                'content_type' => $data['content_type'] ?? "pdf",
                'content_url' => $path,
            ]);

            return $material->load('course');
        });
    }

    public function deleteMaterial(Material $material): bool
    {
        return DB::transaction(function () use ($material) {
            // Delete physical file
            if ($material->content_url) {
                Storage::disk('public')->delete($material->content_url);
            }

            // Delete database record
            return $material->delete();
        });
    }
}
