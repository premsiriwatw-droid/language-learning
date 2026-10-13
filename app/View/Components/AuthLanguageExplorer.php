<?php

namespace App\View\Components;

use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AuthLanguageExplorer extends Component
{
    /** @var array{england: ?Language, china: ?Language} */
    public array $curricula;

    public function __construct()
    {
        $languages = Language::query()
            ->select(['id', 'name'])
            ->whereIn('name', ['English', 'Chinese'])
            ->with([
                'courses' => fn ($query) => $query
                    ->select(['id', 'language_id', 'title'])
                    ->orderBy('id'),
                'courses.units:id,course_id,title,position',
                'courses.units.lessons:id,unit_id,title,position',
            ])
            ->get()
            ->keyBy('name');

        $this->curricula = [
            'england' => $languages->get('English'),
            'china' => $languages->get('Chinese'),
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.auth-language-explorer');
    }
}
