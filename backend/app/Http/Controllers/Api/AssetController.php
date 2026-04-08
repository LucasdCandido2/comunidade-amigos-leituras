<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Services\AssetService;
use App\Services\SignedUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetController extends Controller
{
    public function __construct(
        private AssetService $assetService,
        private SignedUrlService $signedUrlService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'topic_id' => 'nullable|exists:topics,id',
        ]);

        try {
            $asset = $this->assetService->upload(
                $request->file('file'),
                $request->user()->id,
                $request->input('topic_id')
            );

            return response()->json([
                'id' => $asset->id,
                'url' => $asset->url,
                'original_name' => $asset->original_name,
                'mime_type' => $asset->mime_type,
                'size' => $asset->size,
                'size_formatted' => $asset->size_formatted,
                'is_image' => $asset->is_image,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function uploadInline(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info('uploadInline request', [
            'has_file' => $request->hasFile('file'),
            'file' => $request->file('file'),
            'all' => $request->all(),
            'all_files' => $request->allFiles(),
            'headers' => $request->headers->get('Content-Type'),
        ]);
        
        $data = array_merge($request->all(), $request->allFiles());
        
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validação falhou',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }
        
        try {
            $asset = $this->assetService->uploadInlineImage(
                $request->file('file'),
                $request->user()->id
            );

            return response()->json([
                'id' => $asset->id,
                'url' => $asset->url,
                'original_name' => $asset->original_name,
                'mime_type' => $asset->mime_type,
                'size' => $asset->size,
                'size_formatted' => $asset->size_formatted,
                'is_image' => $asset->is_image,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(int $id): StreamedResponse|JsonResponse
    {
        $asset = Asset::findOrFail($id);

        if (!Storage::disk($asset->disk)->exists($asset->path)) {
            return response()->json(['message' => 'Arquivo não encontrado'], 404);
        }

        $stream = $this->assetService->getStream($asset);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $asset->mime_type,
            'Content-Disposition' => 'inline; filename="' . $asset->original_name . '"',
            'Content-Length' => $asset->size,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    public function signedDownload(Request $request, int $id): StreamedResponse|JsonResponse
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'Token não fornecido'], 401);
        }

        $asset = $this->signedUrlService->validateSignedUrl($token);

        if (!$asset || $asset->id !== $id) {
            return response()->json(['message' => 'URL inválida ou expirada'], 403);
        }

        if (!Storage::disk($asset->disk)->exists($asset->path)) {
            return response()->json(['message' => 'Arquivo não encontrado'], 404);
        }

        $stream = $this->assetService->getStream($asset);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $asset->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $asset->original_name . '"',
            'Content-Length' => $asset->size,
        ]);
    }

    public function generateSignedUrl(Request $request, int $id): JsonResponse
    {
        $asset = Asset::findOrFail($id);

        $expiresInMinutes = $request->input('expires_in', 60);
        $expiresInMinutes = min(max((int)$expiresInMinutes, 1), 43200);

        $signedUrl = $this->signedUrlService->generateSignedAssetUrl($asset, $expiresInMinutes);

        return response()->json($signedUrl);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $asset = Asset::findOrFail($id);

        if ($asset->user_id !== $request->user()->id && 
            !$request->user()->hasPermission('delete_any_asset')) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $this->assetService->delete($asset);

        return response()->json(['message' => 'Arquivo excluído com sucesso']);
    }

    public function byTopic(int $topicId): JsonResponse
    {
        $assets = Asset::where('topic_id', $topicId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($asset) => [
                'id' => $asset->id,
                'url' => $asset->url,
                'original_name' => $asset->original_name,
                'mime_type' => $asset->mime_type,
                'size' => $asset->size,
                'size_formatted' => $asset->size_formatted,
                'is_image' => $asset->is_image,
            ]);

        return response()->json($assets);
    }

    public function allowedTypes(): JsonResponse
    {
        return response()->json([
            'allowed_mimes' => AssetService::getAllowedMimes(),
            'allowed_extensions' => AssetService::getAllowedExtensions(),
            'max_size' => AssetService::getMaxSize(),
            'max_size_formatted' => '10 MB',
        ]);
    }
}
