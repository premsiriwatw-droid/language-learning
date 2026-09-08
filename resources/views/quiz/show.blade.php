<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exercise->title }} | Quiz</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4f7fb; color: #172033; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .container { width: min(900px, calc(100% - 32px)); margin: 40px auto; }
        .header, .question-card { background: #fff; border: 1px solid #e4e8ef; border-radius: 18px; box-shadow: 0 8px 30px rgba(23,32,51,.06); }
        .header { padding: 28px; margin-bottom: 18px; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #eef2ff; color: #4338ca; font-size: 13px; font-weight: 700; }
        h1 { margin: 12px 0 4px; font-size: 30px; }
        .muted { color: #697386; }
        .question-card { padding: 24px; margin-bottom: 16px; }
        .question-number { color: #697386; font-size: 14px; font-weight: 700; }
        .question { margin: 8px 0 20px; font-size: 21px; line-height: 1.5; }
        input[type=text] { width: 100%; padding: 13px 14px; border: 1px solid #cfd6e2; border-radius: 10px; font-size: 16px; outline: none; }
        input[type=text]:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .choices { display: grid; gap: 10px; }
        .choice { display: flex; align-items: center; gap: 12px; padding: 14px; border: 1px solid #dce2eb; border-radius: 12px; cursor: pointer; transition: .15s; }
        .choice:hover { border-color: #818cf8; background: #f8f8ff; }
        .choice input { accent-color: #4f46e5; }
        .media-placeholder { display: flex; align-items: center; justify-content: center; min-height: 150px; margin-bottom: 18px; border: 2px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; color: #64748b; text-align: center; }
        .audio-button { border: 0; border-radius: 10px; padding: 12px 18px; background: #4f46e5; color: white; font-weight: 700; cursor: pointer; }
        .audio-button:disabled { opacity: .5; cursor: not-allowed; }
        .submit { width: 100%; border: 0; border-radius: 12px; padding: 15px; background: #111827; color: #fff; font-size: 16px; font-weight: 800; cursor: pointer; }
        .result { margin-top: 16px; padding: 12px 14px; border-radius: 10px; font-weight: 700; }
        .correct { background: #ecfdf5; color: #047857; }
        .incorrect { background: #fef2f2; color: #b91c1c; }
        .explanation { margin-top: 8px; color: #596579; font-size: 14px; font-weight: 400; }
    </style>
</head>
<body>
<div class="container">
    <section class="header">
        <span class="badge">{{ $exercise->type }}</span>
        <h1>{{ $exercise->title }}</h1>
        <div class="muted">{{ $questions->count() }} ข้อ</div>
    </section>

    <form method="POST" action="{{ route('quiz.submit', $exercise) }}">
        @csrf
        @foreach ($questions as $index => $question)
            @php
                $result = $results[$question->id] ?? null;
            @endphp
            <section class="question-card">
                <div class="question-number">ข้อ {{ $index + 1 }}</div>
                <div class="question">{{ $question->question }}</div>

                @if ($exercise->type === 'listening')
                    <div class="media-placeholder">
                        <div>
                            <div style="font-size:32px; margin-bottom:8px">🔊</div>
                            <button type="button" class="audio-button" disabled>เล่นเสียง</button>
                            <div style="margin-top:8px;font-size:13px">รอ Content/Data owner เพิ่ม audio field ใน Question</div>
                        </div>
                    </div>
                    <input type="text" name="answers[{{ $question->id }}]" placeholder="พิมพ์คำตอบภาษาจีน..." value="{{ old('answers.' . $question->id, $result['submitted'] ?? '') }}">
                @elseif ($exercise->type === 'image_choice')
                    <div class="media-placeholder">
                        <div>
                            <div style="font-size:42px; margin-bottom:8px">🖼️</div>
                            <div>Image placeholder</div>
                            <div style="margin-top:6px;font-size:13px">รอ Content/Data owner เพิ่ม image field ใน Question</div>
                        </div>
                    </div>
                    <div class="choices">
                        @foreach ($question->answers as $answer)
                            <label class="choice">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" @checked((string) old('answers.' . $question->id, $result['submitted'] ?? '') === (string) $answer->id)>
                                <span>{{ $answer->answer }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($exercise->type === 'multiple_choice')
                    <div class="choices">
                        @foreach ($question->answers as $answer)
                            <label class="choice">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" @checked((string) old('answers.' . $question->id, $result['submitted'] ?? '') === (string) $answer->id)>
                                <span>{{ $answer->answer }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($exercise->type === 'fill_blank')
                    <input type="text" name="answers[{{ $question->id }}]" placeholder="พิมพ์คำตอบ..." value="{{ old('answers.' . $question->id, $result['submitted'] ?? '') }}">
                @endif

                @if ($result)
                    <div class="result {{ $result['correct'] ? 'correct' : 'incorrect' }}">
                        {{ $result['correct'] ? '✓ ถูกต้อง' : '✗ ยังไม่ถูกต้อง' }}
                        @if (!$result['correct'] && $question->answers->where('is_correct', true)->isNotEmpty())
                            <div class="explanation">คำตอบที่ถูก: {{ $question->answers->where('is_correct', true)->pluck('answer')->join(', ') }}</div>
                        @endif
                    </div>
                @endif
            </section>
        @endforeach

        <button class="submit" type="submit">ตรวจคำตอบ</button>
    </form>
</div>
</body>
</html>
