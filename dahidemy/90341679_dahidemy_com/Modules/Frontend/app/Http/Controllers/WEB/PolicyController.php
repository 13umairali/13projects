<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\Policy\PolicyStoreRequest;
use Modules\Frontend\Http\Requests\Policy\PolicyUpdateRequest;
use Modules\Frontend\Repositories\PolicyRepository;

class PolicyController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private PolicyRepository $policy
    ) {}

    /**
     * Policy List Page
     */
    public function index()
    {
        try {

            $policies = $this->policy->getAll(request()->all());

            return view('frontend::policies.index', compact('policies'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::policies.create');
    }

    /**
     * Store Policy
     */
    public function store(PolicyStoreRequest $request)
    {
        try {

            $this->policy->create($request->all());

            return redirect()
                ->route('policies.index')
                ->with('success', 'Policy created successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit Page
     */
    public function edit(int $id)
    {
        try {

            $policy = $this->policy->getById($id);

            return view('frontend::policies.edit', compact('policy', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update Policy
     */
    public function update(PolicyUpdateRequest $request, int $id)
    {
        try {

            $this->policy->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('policies.index')
                ->with('success', 'Policy updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Policy
     */
    public function destroy(int $id)
    {
        try {

            $policy = $this->policy->getById($id);

            if (! $policy) {

                return back()->with('error', 'Policy Not Found');
            }

            $this->policy->delete($id);

            return redirect()
                ->route('policies.index')
                ->with('success', 'Policy deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
