<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $topic->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #6d28d9;
        }
        
        .header h1 {
            font-size: 24px;
            color: #6d28d9;
            margin-bottom: 10px;
        }
        
        .header .meta {
            font-size: 11px;
            color: #666;
        }
        
        .work-badge {
            display: inline-block;
            background: #f3e8ff;
            color: #6d28d9;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            margin-top: 10px;
        }
        
        .content {
            margin-bottom: 30px;
        }
        
        .content h2 {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .content p {
            text-align: justify;
            margin-bottom: 15px;
            white-space: pre-wrap;
        }
        
        .rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0;
            padding: 10px;
            background: #fef3c7;
            border-radius: 8px;
        }
        
        .interactions {
            margin-top: 30px;
        }
        
        .interactions h2 {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .interaction {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #6d28d9;
        }
        
        .interaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .interaction-author {
            font-weight: bold;
            color: #6d28d9;
        }
        
        .interaction-date {
            font-size: 10px;
            color: #999;
        }
        
        .interaction-rating {
            font-size: 11px;
            color: #f59e0b;
        }
        
        .interaction-content {
            text-align: justify;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        
        @page {
            margin: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $topic->title }}</h1>
        <div class="meta">
            Por {{ $topic->user->name ?? 'Usuário' }} • {{ $topic->created_at->format('d/m/Y H:i') }}
        </div>
        @if($topic->work)
            <div class="work-badge">📚 {{ $topic->work->title }}</div>
        @endif
    </div>

    <div class="content">
        <p>{{ $topic->content }}</p>
        
        @if($topic->rating)
            <div class="rating">
                <span>Avaliação:</span>
                <strong>{{ str_repeat('⭐', $topic->rating) }}</strong>
                <span>({{ $topic->rating }}/5)</span>
            </div>
        @endif
    </div>

    @if($interactions->count() > 0)
        <div class="interactions">
            <h2>💬 Comentários ({{ $interactions->count() }})</h2>
            
            @foreach($interactions as $interaction)
                <div class="interaction">
                    <div class="interaction-header">
                        <span class="interaction-author">{{ $interaction->user->name ?? 'Usuário' }}</span>
                        <span class="interaction-date">{{ $interaction->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($interaction->rating)
                        <div class="interaction-rating">{{ str_repeat('⭐', $interaction->rating) }} {{ $interaction->rating }}/5</div>
                    @endif
                    <div class="interaction-content">
                        <p>{{ $interaction->content }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        Gerado em {{ $generatedAt }}<br>
        Comunidade Amigos Leituras
    </div>
</body>
</html>
