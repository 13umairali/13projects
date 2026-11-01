<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Translation\UserResumeTranslation;
use App\Models\UserResume;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserProfileResumesController extends Controller
{

    public function getForm(Request $request)
    {
        $data = $request->all();
        $html = (string)view()->make('design_1.panel.settings.includes.resumes_modal', $data);

        return response()->json([
            'code' => 200,
            'html' => $html
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'title' => 'required',
            'attach' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!empty($data['user_id'])) {
            $organization = auth()->user();
            $user = User::where('id', $data['user_id'])
                ->where('organ_id', $organization->id)
                ->first();
        } else {
            $user = auth()->user();
        }

        if (!empty($user) and !empty($request->file('attach'))) {
            $resume = UserResume::query()->create([
                'user_id' => $user->id,
                'attach' => null,
                'created_at' => time(),
            ]);

            $this->handleExtraData($request, $resume, $user);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans('update.resume_created_successfully')
            ]);
        }

        return response([], 422);
    }

    public function edit(Request $request, $id)
    {
        $resume = UserResume::query()->findOrFail($id);

        $data = [
            'resume' => $resume,
            'user_id' => $request->get('user_id')
        ];

        $html = (string)view()->make('design_1.panel.settings.includes.resumes_modal', $data);

        return response()->json([
            'code' => 200,
            'html' => $html
        ]);
    }

    public function update(Request $request, $id)
    {
        $resume = UserResume::query()->findOrFail($id);
        $data = $request->all();

        $validator = Validator::make($data, [
            'title' => 'required',
            'attach' => (!empty($resume->attach) ? 'nullable' : 'required') . '|file',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!empty($data['user_id'])) {
            $organization = auth()->user();
            $user = User::where('id', $data['user_id'])
                ->where('organ_id', $organization->id)
                ->first();
        } else {
            $user = auth()->user();
        }

        if (!empty($user) and $resume->user_id == $user->id) {
            $resume->update([
                'user_id' => $user->id,
                'created_at' => time(),
            ]);

            $this->handleExtraData($request, $resume, $user);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans('update.resume_updated_successfully')
            ]);
        }

        return response([
            'code' => 422,
            'errors' => $validator->errors(),
        ], 422);
    }

    public function delete($id)
    {
        $user = auth()->user();

        $resume = UserResume::query()
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                $query->orWhereHas('user', function ($query) use ($user) {
                    $query->where('organ_id', $user->id);
                });
            })
            ->first();

        if (!empty($resume)) {
            $this->removeFile($resume->resume);

            $resume->delete();

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans('update.resume_deleted_successfully')
            ]);
        }

        return response()->json([], 422);
    }

    private function handleExtraData(Request $request, $resume, $user)
    {
        $data = $request->all();

        UserResumeTranslation::query()->updateOrCreate([
            'user_resume_id' => $resume->id,
            'locale' => mb_strtolower($data['locale'])
        ], [
            'title' => $data['title'],
        ]);


        // Upload File
        $path = $resume->attach ?? null;
        $destination = "setting/resumes/{$resume->id}";

        if (!empty($request->file('attach'))) {
            if (!empty($path)) {
                $this->removeFile($path);
            }

            $path = $this->uploadFile($request->file('attach'), $destination, null, $user->id);
        }

        $resume->update([
            'attach' => $path
        ]);
    }

}
