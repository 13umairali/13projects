<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\Http\Requests\ClassLesson\ClassLessonStoreRequest;
use Modules\Teacher\Http\Requests\ClassLesson\ClassLessonUpdateRequest;
use Modules\Teacher\Models\ClassLesson;
use Modules\Teacher\Services\ClassLesson\ClassLessonService;

class ClassLessonController extends Controller
{
    public function __construct(
        private ClassLessonService $service
    ) {}

    public function all(Request $request): JsonResponse
    {
        $classlessons = $this->service->getAll($request);
        if (! $classlessons) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($classlessons, 'ClassLesson has been fetch successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page') ?? 10;
        $classlessons = $this->service->index($request, $perPage);
        if (! $classlessons) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($classlessons, 'ClassLesson has been fetch successfully.');
    }

    public function store(ClassLessonStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $classLesson = new ClassLesson;
            $classLesson->institute_id = get_institute_id();
            $classLesson->branch_id = get_branch_id();
            $classLesson->session_id = get_option('academic_year');
            $classLesson->title = $request->title;
            $classLesson->note = $request->note;
            $classLesson->date = $request->date;
            $classLesson->class_id = $request->class_id;
            $classLesson->section_id = $request->section_id;
            $classLesson->subject_id = $request->subject_id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file = $file_name;
            }
            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file_2 = $file_name;
            }
            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file_3 = $file_name;
            }
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file_4 = $file_name;
            }

            $classLesson->save();

            DB::commit();

            return $this->responseSuccess([], 'ClassLesson has been create successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function show(int $id): JsonResponse
    {
        $classlesson = $this->service->getById($id);
        if (! $classlesson) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($classlesson, 'ClassLesson has been show successfully.');
    }

    public function update(ClassLessonUpdateRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $classLesson = ClassLesson::find($id);
            $classLesson->session_id = get_option('academic_year');
            $classLesson->title = $request->title;
            $classLesson->note = $request->note;
            $classLesson->date = $request->date;
            $classLesson->class_id = $request->class_id;
            $classLesson->section_id = $request->section_id;
            $classLesson->subject_id = $request->subject_id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file = $file_name;
            }
            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file_2 = $file_name;
            }
            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file_3 = $file_name;
            }
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/files/class_lessons/'), $file_name);
                $classLesson->file_4 = $file_name;
            }

            $classLesson->save();

            DB::commit();

            return $this->responseSuccess([], 'ClassLesson has been update successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->delete($id);
            if (! $data) {
                return $this->responseError([], 'Data Not Found!', 204);
            }

            DB::commit();

            return $this->responseSuccess($data, 'ClassLesson has been delete successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Not Found!');
        }
    }
}
