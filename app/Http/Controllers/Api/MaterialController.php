<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Material\StoreMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Services\MaterialService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    use ApiResponse;

    protected MaterialService $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    /**
     * Get materials by course ID.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $materials = Material::where('course_id', $request->course_id)
            ->with('course:id,title')
            ->latest()
            ->get();

        return $this->successResponse(
            MaterialResource::collection($materials),
            'List of materials'
        );
    }

    /**
     * Upload a new material.
     */
    public function store(StoreMaterialRequest $request): JsonResponse
    {
        // $this->authorize('create', Material::class);
        Gate::authorize('create', Material::class);

        $material = $this->materialService->createMaterial(
            $request->validated(),
            $request->file('file')
        );

        return $this->successResponse(
            new MaterialResource($material),
            'Material uploaded successfully',
            201
        );
    }

    /**
     * Delete material.
     */
    public function destroy(Material $material): JsonResponse
    {
        // $this->authorize('delete', $material);
        Gate::authorize('delete', $material);

        $this->materialService->deleteMaterial($material);

        return $this->successResponse(null, 'Material deleted successfully');
    }
}
