<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A lesson written by the teacher inside their own course: text, one document and one video.
 * Files live on the private "local" disk and are only served through authorized routes.
 */
class TeacherLesson extends Model
{
    public const DISK = 'local';

    public const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];

    /** Uploaded video extension => content type sent to the browser. */
    public const VIDEO_TYPES = ['mp4' => 'video/mp4', 'webm' => 'video/webm'];

    public const DOCUMENT_MAX_KB = 20480;

    public const VIDEO_MAX_KB = 102400;

    // File columns and position are written by the controller only.
    protected $fillable = ['title', 'body', 'video_url'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(TeacherCourse::class, 'teacher_course_id');
    }

    public function hasDocument(): bool
    {
        return $this->document_path !== null;
    }

    public function hasVideoFile(): bool
    {
        return $this->video_path !== null;
    }

    public function hasVideo(): bool
    {
        return $this->hasVideoFile() || $this->videoEmbedUrl() !== null;
    }

    public function videoContentType(): string
    {
        return self::VIDEO_TYPES[strtolower(pathinfo((string) $this->video_path, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
    }

    /** Only YouTube and Vimeo links are embedded; anything else is never put in an iframe. */
    public function videoEmbedUrl(): ?string
    {
        return self::embedUrlFor($this->video_url);
    }

    public static function embedUrlFor(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $parts = parse_url(trim($url));
        if (! is_array($parts) || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower(preg_replace('/^(www\.|m\.)/', '', $parts['host'] ?? ''));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $youtubeId = match (true) {
            $host === 'youtu.be' => ltrim($path, '/'),
            $host === 'youtube.com' && $path === '/watch' => $query['v'] ?? '',
            $host === 'youtube.com' && preg_match('#^/(embed|shorts|live)/([^/]+)#', $path, $m) === 1 => $m[2],
            default => null,
        };

        if ($youtubeId !== null) {
            return preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeId) === 1
                ? 'https://www.youtube-nocookie.com/embed/'.$youtubeId
                : null;
        }

        if ($host === 'vimeo.com' && preg_match('#^/(\d{6,12})/?$#', $path, $m) === 1) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    public static function sizeLabel(?int $bytes): string
    {
        if ($bytes === null) {
            return '';
        }

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1).' MB'
            : number_format(max(1, $bytes / 1024)).' KB';
    }
}
