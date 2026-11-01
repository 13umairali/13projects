<?php

namespace App\Http\Controllers\Panel;


use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobMyBookmarksController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize("panel_jobs_my_bookmarks");

        $user = auth()->user();

        $query = Favorite::query()->where('user_id', $user->id);
        $query->whereNotNull('job_id');
        $query->whereHas('job');

        $getListData = $this->getListsData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }


        $data = [
            'pageTitle' => trans('update.bookmarked_jobs'),
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.jobs.my_bookmarks.index', $data);
    }

    private function getListsData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $bookmarks = $query
            ->with([
                'job' => function ($query) {
                    $query->with([
                        'employmentType',
                        'experienceLevel',
                        'category',
                        'creator' => function ($query) {
                            $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'email', 'mobile');
                        },
                    ]);
                }
            ])
            ->get();


        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $bookmarks, $total, $count);
        }

        return [
            'bookmarks' => $bookmarks,
            'pagination' => $this->makePagination($request, $bookmarks, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $bookmarks, $total, $count)
    {
        $html = "";

        foreach ($bookmarks as $bookmarkRow) {
            $html .= (string)view()->make("design_1.panel.jobs.my_bookmarks.table_items", ['bookmark' => $bookmarkRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $bookmarks, $total, $count, true)
        ]);
    }

    public function delete($bookmarkId)
    {
        $user = auth()->user();

        $bookmark = Favorite::query()->where('id', $bookmarkId)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($bookmark)) {
            $bookmark->delete();

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans('update.bookmark_job_deleted_successfully'),
            ]);
        }

        return response()->json([], 422);
    }

}
