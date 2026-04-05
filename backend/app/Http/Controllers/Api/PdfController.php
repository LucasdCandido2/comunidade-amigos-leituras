<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PdfService;
use Illuminate\Http\JsonResponse;

class PdfController extends Controller
{
    public function exportTopic(int $topicId): JsonResponse
    {
        try {
            $pdfService = new PdfService();
            $pdfService->generateTopicPdf($topicId);
            return $pdfService->streamTopicPdf($topicId);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
