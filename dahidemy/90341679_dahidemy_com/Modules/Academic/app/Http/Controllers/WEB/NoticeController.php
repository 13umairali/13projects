<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\UserNotice;
use RealRashid\SweetAlert\Facades\Alert;

class NoticeController extends Controller
{
    public const TYPES = ['Notice', 'Circular', 'Appointment Letter', 'Dismissal Letter'];

    public function index(): Factory|View
    {
        // Was Notice::all() -- completely unscoped, every institute could
        // see every other institute's notices/letters.
        $notices = Notice::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderByDesc('id')
            ->get();

        return view('academic::notice.list', compact('notices'));
    }

    public function create(Request $request): Factory|View
    {
        $roles = DB::table('roles')->get();
        $staffs = Staff::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        return view('academic::notice.create', compact('roles', 'staffs'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string',
            'notice' => 'required|string',
            'type' => 'nullable|in:'.implode(',', self::TYPES),
            'recipient_staff_id' => 'nullable|integer|exists:staffs,id',
            // Appointment/Dismissal letters address one specific staff
            // member rather than broadcasting to a user_type group.
            'user_type' => 'required_if:type,Notice,Circular',
            'image' => 'nullable|image|max:2048',
        ]);

        $userTypes = is_string($request->user_type) ? json_decode($request->user_type, true) : $request->user_type;
        if ($request->filled('user_type') && ! is_array($userTypes)) {
            return back();
        }

        $notice = new Notice;
        $notice->institute_id = get_institute_id();
        $notice->branch_id = get_branch_id();
        $notice->title = $request->title;
        $notice->type = $request->type ?: 'Notice';
        $notice->recipient_staff_id = $request->recipient_staff_id ?: null;
        $notice->display_on_website = $request->boolean('display_on_website');
        $notice->notice = $request->notice;
        $notice->date = Carbon::now();
        $notice->created_by = Auth::id();
        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $notice->image = fileUploader('notices/', 'png', $request['image']);
        }
        $notice->save();

        foreach ((array) $userTypes as $user_type) {
            UserNotice::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'notice_id' => $notice->id,
                'user_type' => $user_type,
            ]);
        }

        Alert::toast('Notice has been create successfully.', 'success');

        return redirect('notices');
    }

    public function show(Request $request, int $id): Factory|View
    {
        $notice = Notice::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->findOrFail($id);

        return view('academic::notice.view', compact('notice', 'id'));
    }

    public function edit(Request $request, int $id): Factory|View
    {
        $notice = Notice::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->findOrFail($id);
        $roles = DB::table('roles')->get();
        $staffs = Staff::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        return view('academic::notice.edit', compact('notice', 'id', 'roles', 'staffs'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'notice' => 'required',
            'type' => 'nullable|in:'.implode(',', self::TYPES),
            'recipient_staff_id' => 'nullable|integer|exists:staffs,id',
            'image' => 'nullable',
        ]);

        $notice = Notice::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);
        if (! $notice) {
            return back();
        }
        $notice->title = $request->title;
        $notice->type = $request->type ?: $notice->type;
        $notice->recipient_staff_id = $request->recipient_staff_id ?: null;
        $notice->display_on_website = $request->boolean('display_on_website');
        $notice->notice = $request->notice;
        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $notice->image = fileUploader('notices/', 'png', $request['image'], $notice->image);
        }
        $notice->save();

        UserNotice::where('notice_id', $id)->delete();

        foreach ((array) $request->user_type as $user_type) {
            $userNotice = new UserNotice;
            $userNotice->institute_id = get_institute_id();
            $userNotice->branch_id = get_branch_id();
            $userNotice->notice_id = $notice->id;
            $userNotice->user_type = $user_type;
            $userNotice->save();
        }

        Alert::toast('Notice has been update successfully.', 'success');

        return redirect('notices');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $notice = Notice::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        if (! $notice) {
            Alert::error('Error', _lang('Notice not found.'));

            return redirect('notices');
        }

        $notice->delete();

        Alert::toast('Notice has been delete successfully.', 'success');

        return redirect('notices');
    }

    /**
     * "Allow sending these via email and Export/Print to PDF."
     */
    public function exportPdf(int $id)
    {
        $notice = Notice::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->findOrFail($id);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('academic::notice.pdf', compact('notice'))
            ->setPaper('a4', 'portrait')
            ->download(str($notice->title)->slug().'.pdf');
    }

    public function email(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $notice = Notice::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->findOrFail($id);

        try {
            \Illuminate\Support\Facades\Mail::to($request->email)->send(
                new \App\Mail\NoticeMail($notice)
            );
            Alert::toast(_lang('Sent successfully.'), 'success');
        } catch (\Exception $e) {
            Alert::error('Error', _lang('Could not send the email. Please try again.'));
        }

        return redirect()->back();
    }
}
