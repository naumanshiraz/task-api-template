<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private ProjectService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $projects = $this->service->getUserProjects(
                $request->user()->id,
                $request->input('per_page', 15)
            );

            return response()->json([
                'success' => true,
                'data' => ProjectResource::collection($projects),
                'meta' => $this->getPaginationMeta($projects),
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Failed to fetch projects', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = $request->user()->id;

            $project = $this->service->createProject($validated);

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
                'message' => 'Project created successfully',
            ], 201);
        } catch (\Exception $e) {
            return $this->apiError('Failed to create project', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        try {
            $project = $this->service->getProjectById($id);

            $this->authorize('view', $project);

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Project not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, int $id)
    {
        try {
            $project = $this->service->getProjectById($id);

            $this->authorize('update', $project);

            $validated = $request->validated();
            $project = $this->service->updateProject($id, $validated);

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
                'message' => 'Project updated successfully',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Project not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $project = $this->service->getProjectById($id);

            $this->authorize('delete', $project);

            $this->service->deleteProject($id);

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Project not found', 404);
        }
    }

    /**
     * Get pagination metadata.
     */
    protected function getPaginationMeta($paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}