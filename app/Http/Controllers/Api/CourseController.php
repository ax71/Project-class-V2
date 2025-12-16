<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    use ApiResponse;

    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Get all courses with pagination.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with('instructor:id,name')
            ->withCount(['materials', 'quizzes'])
            ->latest()
            ->paginate(15);

        return $this->paginatedResponse(
            $courses->through(fn($course) => new CourseResource($course)),
            'List of courses'
        );
    }

    /**
     * Create a new course.
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->createCourse(
            array_merge($request->validated(), ['user_id' => auth()->id()]),
            $request->file('cover_image')
        );

        return $this->successResponse(
            new CourseResource($course),
            'Course created successfully',
            201
        );
    }

    /**
     * Get course details.
     */
    public function show(int $id): JsonResponse
    {
        $course = Course::with(['instructor:id,name', 'materials', 'quizzes'])
            ->find($id);

        if (!$course) {
            return $this->errorResponse('Course not found', 404);
        }

        return $this->successResponse(
            new CourseResource($course),
            'Course details'
        );
    }

    /**
     * Update course.
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $updatedCourse = $this->courseService->updateCourse(
            $course,
            $request->validated(),
            $request->file('cover_image')
        );

        return $this->successResponse(
            new CourseResource($updatedCourse),
            'Course updated successfully'
        );
    }

    /**
     * Delete course.
     */
    public function destroy(Course $course): JsonResponse
    {
        // $this->authorize('delete', $course);
        Gate::authorize('delete', $course);
        

        $this->courseService->deleteCourse($course);

        return $this->successResponse(null, 'Course deleted successfully');
    }

    /**
     * Delete only the cover image.
     */
    public function deleteCoverImage(Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $deleted = $this->courseService->deleteCoverImage($course);

        if (!$deleted) {
            return $this->errorResponse('No cover image to delete', 404);
        }

        return $this->successResponse(null, 'Cover image deleted successfully');
    }
}
