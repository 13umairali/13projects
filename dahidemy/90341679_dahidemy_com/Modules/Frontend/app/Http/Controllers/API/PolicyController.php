<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\Policy\PolicyStoreRequest;
use Modules\Frontend\Http\Requests\Policy\PolicyUpdateRequest;
use Modules\Frontend\Repositories\PolicyRepository;

class PolicyController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private PolicyRepository $policy) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->policy->getAll(request()->all()),
                'Policy has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(PolicyStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->policy->create($request->all()),
                'Policy has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->policy->getById($id),
                'Policy has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(PolicyUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->policy->update($id, $this->getUpdateRequest($request)),
                'Policy has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->policy->delete($id),
                'Policy has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
