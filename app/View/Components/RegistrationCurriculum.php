<?php

namespace App\View\Components;

use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Component;

class RegistrationCurriculum extends Component
{
    public array $curricula;

    public function __construct()
    {
        $this->curricula = [
            'england' => [
                'name' => 'ภาษาอังกฤษ',
                'label' => 'ENGLISH / COURSE PREVIEW',
                'courses' => collect(),
            ],
            'china' => [
                'name' => 'ภาษาจีน',
                'label' => '中文 / COURSE PREVIEW',
                'courses' => collect(),
            ],
        ];

        foreach (['languages', 'courses', 'units', 'lessons'] as $table) {
            if (! Schema::hasTable($table)) {
                return;
            }
        }

        $languages = Language::query()->select(['id', 'name'])->orderBy('id')->get()
            ->filter(fn (Language $language) => $this->sceneFor($language->name) !== null);

        $languages->load([
            'courses' => fn ($query) => $query->select(['id', 'language_id', 'title'])->orderBy('id'),
            'courses.units' => fn ($query) => $query->select(['id', 'course_id', 'title', 'position']),
            'courses.units.lessons' => fn ($query) => $query->select(['id', 'unit_id', 'title', 'position']),
        ]);

        foreach ($languages as $language) {
            $scene = $this->sceneFor($language->name);
            $this->curricula[$scene]['courses'] = $this->curricula[$scene]['courses']->concat($language->courses);
        }
    }

    private function sceneFor(string $name): ?string
    {
        return match (mb_strtolower(trim($name))) {
            'english', 'en', 'ภาษาอังกฤษ', 'อังกฤษ' => 'england',
            'chinese', 'mandarin', 'zh', 'zh-cn', 'ภาษาจีน', 'จีน', '中文', '汉语', '漢語' => 'china',
            default => null,
        };
    }

    public function render(): View
    {
        return view('components.registration-curriculum');
    }
}
