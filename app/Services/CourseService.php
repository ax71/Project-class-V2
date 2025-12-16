<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseService
{
    /**
     * Create a new course with optional cover image.
     *
     * @param array $data
     * @param UploadedFile|null $coverImage
     * @return Course
     */
    public function createCourse(array $data, ?UploadedFile $coverImage = null): Course
    {
        return DB::transaction(function () use ($data, $coverImage) {
            // Handle cover image upload
            if ($coverImage) {
                $data['cover_image'] = $coverImage->store('courses/covers', 'public');
            }

            $course = Course::create($data);

            return $course->load('instructor');
        });
    }

    /**
     * Update course with optional cover image.
     *
     * @param Course $course
     * @param array $data
     * @param UploadedFile|null $coverImage
     * @return Course
     */
    public function updateCourse(Course $course, array $data, ?UploadedFile $coverImage = null): Course
    {
        return DB::transaction(function () use ($course, $data, $coverImage) {
            // Handle new cover image upload
            if ($coverImage) {
                // Delete old cover image if exists
                if ($course->cover_image) {
                    Storage::disk('public')->delete($course->cover_image);
                }

                $data['cover_image'] = $coverImage->store('courses/covers', 'public');
            }

            $course->update($data);

            return $course->fresh(['instructor']);
        });
    }

    /**
     * Delete course and its cover image.
     *
     * @param Course $course
     * @return bool
     */
    public function deleteCourse(Course $course): bool
    {
        return DB::transaction(function () use ($course) {
            // Delete cover image if exists
            if ($course->cover_image) {
                Storage::disk('public')->delete($course->cover_image);
            }

            return $course->delete();
        });
    }

    /**
     * Delete only the cover image.
     *
     * @param Course $course
     * @return bool
     */
    public function deleteCoverImage(Course $course): bool
    {
        if (!$course->cover_image) {
            return false;
        }

        Storage::disk('public')->delete($course->cover_image);
        $course->update(['cover_image' => null]);

        return true;
    }
}
