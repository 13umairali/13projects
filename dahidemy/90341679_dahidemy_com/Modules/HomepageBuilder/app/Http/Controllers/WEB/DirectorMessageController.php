<?php

namespace Modules\HomepageBuilder\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HomepageBuilder\Models\DirectorMessage;
use RealRashid\SweetAlert\Facades\Alert;

class DirectorMessageController extends Controller
{
    public function edit()
    {
        $message = DirectorMessage::firstOrNew(['institute_id' => get_institute_id()]);

        return view('homepagebuilder::admin.director-message', compact('message'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'director_name' => ['nullable', 'string', 'max:191'],
            'designation' => ['nullable', 'string', 'max:191'],
            'message' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $message = DirectorMessage::firstOrNew(['institute_id' => get_institute_id()]);
        unset($validated['photo']); // handled separately below, so editing without re-uploading doesn't wipe the existing photo
        $message->fill($validated);
        $message->institute_id = get_institute_id();
        $message->branch_id = get_branch_id();
        $message->created_by = $message->created_by ?: auth()->id();

        if ($request->hasFile('photo')) {
            $message->photo = fileUploader('director_messages/', $request->file('photo')->getClientOriginalExtension(), $request->file('photo'), $message->photo);
        }

        $message->save();

        Alert::success('Success', 'Director\'s Message updated.');

        return redirect()->route('homepagebuilder.admin.director-message.edit');
    }
}
