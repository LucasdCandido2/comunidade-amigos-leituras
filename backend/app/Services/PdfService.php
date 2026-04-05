<?php

namespace App\Services;

use App\Models\Topic;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generateTopicPdf(int $topicId): string
    {
        $topic = Topic::with(['user:id,name', 'work:id,title', 'interactions.user:id,name'])
            ->findOrFail($topicId);

        $data = [
            'topic' => $topic,
            'interactions' => $topic->interactions->where('is_visible', true),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.topic', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'topico-' . $topic->id . '-' . time() . '.pdf';
        
        return $pdf->download($filename);
    }

    public function streamTopicPdf(int $topicId)
    {
        $topic = Topic::with(['user:id,name', 'work:id,title', 'interactions.user:id,name'])
            ->findOrFail($topicId);

        $data = [
            'topic' => $topic,
            'interactions' => $topic->interactions->where('is_visible', true),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.topic', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('topico-' . $topic->id . '.pdf');
    }
}
