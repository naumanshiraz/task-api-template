<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use App\Services\TaskService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private CommentService $service,
        private TaskService $taskService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $projectId, int $taskId)
    {
        try {
            $task = $this->taskService->getTaskById($taskId);

            $comments = $this->service->getTaskComments(
                $taskId,
                $request->input('per_page', 15)
            );

            return response()->json([
                'success' => true,
                'data' => CommentResource::collection($comments),
                'meta' => $this->getPaginationMeta($comments),
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Failed to fetch comments', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request, int $projectId, int $taskId)
    {
        try {
            $task = $this->taskService->getTaskById($taskId);

            $validated = $request->validated();
            $validated['task_id'] = $taskId;
            $validated['user_id'] = $request->user()->id;

            $comment = $this->service->createComment($validated);

            return response()->json([
                'success' => true,
                'data' => new CommentResource($comment),
                'message' => 'Comment created successfully',
            ], 201);
        } catch (\Exception $e) {
            return $this->apiError('Failed to create comment', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, int $commentId)
    {
        try {
            $comment = $this->service->getCommentById($commentId);

            $this->authorize('update', $comment);

            $validated = $request->validated();
            $comment = $this->service->updateComment($commentId, $validated);

            return response()->json([
                'success' => true,
                'data' => new CommentResource($comment),
                'message' => 'Comment updated successfully',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Comment not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $commentId)
    {
        try {
            $comment = $this->service->getCommentById($commentId);

            $this->authorize('delete', $comment);

            $this->service->deleteComment($commentId);

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Comment not found', 404);
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