<?php

namespace Modules\PopupBroadcaster\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\PopupBroadcaster\Models\Popup;
use RealRashid\SweetAlert\Facades\Alert;

class PopupBroadcasterController extends Controller
{
    public function index()
    {
        $popups = Popup::where('institute_id', get_institute_id())
            ->orderByDesc('id')
            ->paginate(15);

        return view('popupbroadcaster::admin.index', compact('popups'));
    }

    public function create()
    {
        return view('popupbroadcaster::admin.form', ['popup' => new Popup]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = fileUploader('popups/', 'png', $request->file('image'));
        }

        Popup::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'created_by' => auth()->id(),
        ]);

        Alert::success('Success', 'Popup created.');

        return redirect()->route('popupbroadcaster.index');
    }

    public function edit(int $id)
    {
        $popup = Popup::where('institute_id', get_institute_id())->findOrFail($id);

        return view('popupbroadcaster::admin.form', compact('popup'));
    }

    public function update(Request $request, int $id)
    {
        $popup = Popup::where('institute_id', get_institute_id())->findOrFail($id);
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = fileUploader('popups/', 'png', $request->file('image'), $popup->image);
        }

        $popup->update($validated);

        Alert::success('Success', 'Popup updated.');

        return redirect()->route('popupbroadcaster.index');
    }

    public function destroy(int $id)
    {
        $popup = Popup::where('institute_id', get_institute_id())->findOrFail($id);
        if ($popup->image) {
            fileRemover('popups/', $popup->image);
        }
        $popup->delete();

        Alert::success('Success', 'Popup deleted.');

        return redirect()->route('popupbroadcaster.index');
    }

    public function toggleStatus(int $id)
    {
        $popup = Popup::where('institute_id', get_institute_id())->findOrFail($id);
        $popup->status = $popup->isActive() ? Popup::STATUS_INACTIVE : Popup::STATUS_ACTIVE;
        $popup->save();

        return back();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'message' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_link' => ['nullable', 'string', 'max:500'],
            'audience' => ['required', 'in:everyone,logged_in_only,students_only,parents_only'],
            'display_frequency' => ['required', 'in:every_visit,once_per_session,once_ever'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'priority' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
        ]);

        unset($data['image']); // handled separately (upload path, not the file itself)
        $data['priority'] = $data['priority'] ?? 0;
        $data['status'] = $request->boolean('status', true) ? Popup::STATUS_ACTIVE : Popup::STATUS_INACTIVE;

        return $data;
    }
}
