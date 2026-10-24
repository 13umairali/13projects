<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\Institute\InstituteStoreRequest;
use Modules\Authentication\Http\Requests\Institute\InstituteUpdateRequest;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Repositories\InstituteRepository;

class InstituteController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private InstituteRepository $institute) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->institute->getAll(request()->all()),
                'Institute has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(InstituteStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->institute->create($request->all()),
                'Institute has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->institute->getById($id),
                'Institute has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(InstituteUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->institute->update($id, $this->getUpdateRequest($request)),
                'Institute has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $institute = $this->institute->getById($id);
            if (! $institute) {
                return $this->responseError([], 'Institute Not Found');
            }
            if ($institute->logo) {
                fileUploader('institute/', 'png', null, $institute->logo);
            }

            return $this->responseSuccess(
                $this->institute->delete($id),
                'Institute has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function statusUpdate(int $instituteId): JsonResponse
    {
        try {
            $institute = Institute::find($instituteId);

            if ($institute) {
                $institute->status = $institute->status == 1 ? 0 : 1;
                $institute->save();

                return $this->responseSuccess(
                    ['status' => $institute->status],
                    'Institute status updated successfully.'
                );
            }

            return $this->responseError([], 'Institute not found.', 404);
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
