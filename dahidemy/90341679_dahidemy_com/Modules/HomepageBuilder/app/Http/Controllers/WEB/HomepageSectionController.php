<?php

namespace Modules\HomepageBuilder\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Modules\HomepageBuilder\Models\HomepageSection;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::forInstitute(get_institute_id(), get_branch_id());
        $registry = HomepageSection::REGISTRY;

        return view('homepagebuilder::admin.sections', compact('sections', 'registry'));
    }

    public function toggle(int $id)
    {
        $section = HomepageSection::where('institute_id', get_institute_id())->findOrFail($id);
        $section->is_enabled = ! $section->is_enabled;
        $section->save();

        return back();
    }

    public function moveUp(int $id)
    {
        $this->swap($id, 'up');

        return back();
    }

    public function moveDown(int $id)
    {
        $this->swap($id, 'down');

        return back();
    }

    private function swap(int $id, string $direction): void
    {
        $section = HomepageSection::where('institute_id', get_institute_id())->findOrFail($id);

        $sibling = $direction === 'up'
            ? HomepageSection::where('institute_id', get_institute_id())->where('sort_order', '<', $section->sort_order)->orderByDesc('sort_order')->first()
            : HomepageSection::where('institute_id', get_institute_id())->where('sort_order', '>', $section->sort_order)->orderBy('sort_order')->first();

        if (! $sibling) {
            return;
        }

        [$a, $b] = [$section->sort_order, $sibling->sort_order];
        $section->update(['sort_order' => $b]);
        $sibling->update(['sort_order' => $a]);
    }
}
