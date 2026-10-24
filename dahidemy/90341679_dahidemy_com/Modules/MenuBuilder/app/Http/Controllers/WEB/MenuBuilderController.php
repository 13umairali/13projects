<?php

namespace Modules\MenuBuilder\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\MenuBuilder\Models\CustomMenu;
use RealRashid\SweetAlert\Facades\Alert;

class MenuBuilderController extends Controller
{
    /**
     * The site's current hardcoded nav, offered as a one-click starting point
     * so an admin customizing their menu isn't starting from a blank slate.
     */
    private const DEFAULT_ITEMS = [
        ['label' => 'Home', 'link_type' => 'home', 'link_value' => null],
        ['label' => 'About', 'link_type' => 'anchor', 'link_value' => 'about'],
        ['label' => 'Academics', 'link_type' => 'academics_pages', 'link_value' => null],
        ['label' => 'Admission', 'link_type' => 'route', 'link_value' => 'admission-form-page'],
        ['label' => 'Results', 'link_type' => 'route', 'link_value' => 'result-page'],
        ['label' => 'Gallery', 'link_type' => 'route', 'link_value' => 'academic-images'],
        ['label' => 'Events', 'link_type' => 'route', 'link_value' => 'eventenrollment.index'],
        ['label' => 'Forms', 'link_type' => 'route', 'link_value' => 'public-form.index'],
        ['label' => 'FAQ', 'link_type' => 'anchor', 'link_value' => 'faq'],
        ['label' => 'Contact', 'link_type' => 'route', 'link_value' => 'contact'],
    ];

    public function index()
    {
        $items = CustomMenu::where('institute_id', get_institute_id())
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')])
            ->get();

        return view('menubuilder::admin.index', compact('items'));
    }

    public function create()
    {
        $parents = CustomMenu::where('institute_id', get_institute_id())
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('menubuilder::admin.form', [
            'item' => new CustomMenu(['link_type' => 'custom_url']),
            'parents' => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($error = $this->missingLinkValueError($validated)) {
            return back()->withInput()->with('error', $error);
        }

        $maxOrder = CustomMenu::where('institute_id', get_institute_id())
            ->where('parent_id', $validated['parent_id'])
            ->max('sort_order');

        CustomMenu::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'sort_order' => ($maxOrder ?? 0) + 1,
        ]);

        Alert::success('Success', 'Menu item added.');

        return redirect()->route('menubuilder.index');
    }

    public function edit(int $id)
    {
        $item = CustomMenu::where('institute_id', get_institute_id())->findOrFail($id);

        $parents = CustomMenu::where('institute_id', get_institute_id())
            ->whereNull('parent_id')
            ->where('id', '!=', $item->id)
            ->orderBy('sort_order')
            ->get();

        return view('menubuilder::admin.form', compact('item', 'parents'));
    }

    public function update(Request $request, int $id)
    {
        $item = CustomMenu::where('institute_id', get_institute_id())->findOrFail($id);
        $validated = $this->validated($request);

        if ($error = $this->missingLinkValueError($validated)) {
            return back()->withInput()->with('error', $error);
        }

        // A top-level item can't become its own child, and can't be nested
        // under one of its own children — both would create a cycle.
        if ($validated['parent_id'] === $item->id) {
            return back()->withInput()->with('error', 'A menu item cannot be its own parent.');
        }

        $item->update($validated);

        Alert::success('Success', 'Menu item updated.');

        return redirect()->route('menubuilder.index');
    }

    public function destroy(int $id)
    {
        $item = CustomMenu::where('institute_id', get_institute_id())->findOrFail($id);
        $item->children()->delete();
        $item->delete();

        Alert::success('Success', 'Menu item deleted.');

        return redirect()->route('menubuilder.index');
    }

    public function toggleStatus(int $id)
    {
        $item = CustomMenu::where('institute_id', get_institute_id())->findOrFail($id);
        $item->status = $item->isActive() ? CustomMenu::STATUS_INACTIVE : CustomMenu::STATUS_ACTIVE;
        $item->save();

        return back();
    }

    public function moveUp(int $id)
    {
        $this->swapWithSibling($id, direction: 'up');

        return back();
    }

    public function moveDown(int $id)
    {
        $this->swapWithSibling($id, direction: 'down');

        return back();
    }

    /**
     * One-click starting point: only runs if this institute has no menu
     * items yet, so it can never silently duplicate or overwrite a menu
     * someone has already built.
     */
    public function resetToDefaults()
    {
        $existing = CustomMenu::where('institute_id', get_institute_id())->exists();

        if ($existing) {
            Alert::error('Error', 'You already have menu items — remove them first if you want to start over, to avoid losing anything by accident.');

            return redirect()->route('menubuilder.index');
        }

        foreach (self::DEFAULT_ITEMS as $index => $item) {
            CustomMenu::create([
                ...$item,
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'sort_order' => $index,
                'status' => CustomMenu::STATUS_ACTIVE,
            ]);
        }

        Alert::success('Success', 'Default menu created — customize it as you like.');

        return redirect()->route('menubuilder.index');
    }

    private function validated(Request $request): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:100'],
            'link_type' => ['required', 'in:home,anchor,route,custom_url,academics_pages'],
            'open_new_tab' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:custom_menus,id'],
        ];

        $data = $request->validate($rules);
        $data['open_new_tab'] = $request->boolean('open_new_tab');
        $data['parent_id'] = $request->input('parent_id') ?: null;

        $data['link_value'] = match ($data['link_type']) {
            'anchor' => $request->input('anchor_value'),
            'route' => $request->input('route_value'),
            'custom_url' => $request->input('custom_url_value'),
            default => null,
        };

        if (in_array($data['link_type'], ['anchor', 'route', 'custom_url'], true) && empty($data['link_value'])) {
            abort(422, 'Please provide a value for the selected link type.');
        }

        return $data;
    }

    /**
     * Returns a friendly error string if the chosen link type requires a
     * value that wasn't provided, or null if everything's fine.
     */
    private function missingLinkValueError(array $data): ?string
    {
        if (in_array($data['link_type'], ['anchor', 'route', 'custom_url'], true) && empty($data['link_value'])) {
            return 'Please provide a value for the selected link type.';
        }

        return null;
    }

    private function swapWithSibling(int $id, string $direction): void
    {
        $item = CustomMenu::where('institute_id', get_institute_id())->findOrFail($id);

        $siblingQuery = CustomMenu::where('institute_id', get_institute_id())
            ->where('parent_id', $item->parent_id);

        $sibling = $direction === 'up'
            ? $siblingQuery->where('sort_order', '<', $item->sort_order)->orderByDesc('sort_order')->first()
            : $siblingQuery->where('sort_order', '>', $item->sort_order)->orderBy('sort_order')->first();

        if (! $sibling) {
            return;
        }

        [$itemOrder, $siblingOrder] = [$item->sort_order, $sibling->sort_order];
        $item->update(['sort_order' => $siblingOrder]);
        $sibling->update(['sort_order' => $itemOrder]);
    }
}
