<?php

namespace App\Http\Middleware;

use App\Models\Lesson;
use App\Services\Progress\LearningProgress;
use Closure;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RememberLearningVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $view = method_exists($response, 'getOriginalContent') ? $response->getOriginalContent() : null;

        // Read the existing controller's rendered step; do not duplicate lesson/quiz logic.
        if ($request->isMethod('GET') && $request->user() && $response->isSuccessful()
            && $view instanceof View && $view->name() === 'frontend.learn-step') {
            $data = $view->getData();
            if (($data['lesson'] ?? null) instanceof Lesson && isset($data['step'])) {
                app(LearningProgress::class)->visit($request->user(), $data['lesson'], (int) $data['step']);
            }
        }

        return $response;
    }
}
