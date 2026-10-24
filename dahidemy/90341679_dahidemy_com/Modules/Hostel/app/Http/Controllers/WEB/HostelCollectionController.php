<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Hostel\Http\Requests\HostelCollection\HostelCollectionStoreRequest;
use Modules\Hostel\Models\HostelCollection;
use Modules\Hostel\Models\RoomMember;
use Modules\Hostel\Services\HostelCollectionService;
use RealRashid\SweetAlert\Facades\Alert;

class HostelCollectionController extends Controller
{
    use HasPermissionWebTrait;

    public function __construct(private readonly HostelCollectionService $hostelCollectionService) {}

    public function index(Request $request): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_collection_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $currentMonth = $request->month ?: now()->format('Y-m');

        $members = RoomMember::with(['student.user', 'room.hostelCategory', 'bed', 'collections' => function ($query) use ($currentMonth) {
            $query->where('month', $currentMonth);
        }])
            ->where('status', 'active')
            ->get();

        return view('hostel::hostel_collection.index', compact('members', 'currentMonth'));
    }

    public function create(int $roomMemberId): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_collection_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $roomMember = RoomMember::with(['student.user', 'room.hostelCategory', 'bed'])->findOrFail($roomMemberId);
        $ledgers = AccountingLedger::all();
        $funds = AccountingFund::all();

        $feeConfig = $this->hostelCollectionService->getFeeConfig(
            $roomMember->room->hostel_category_id,
            (int) get_option('academic_year')
        );

        return view('hostel::hostel_collection.create', compact('roomMember', 'ledgers', 'funds', 'feeConfig'));
    }

    public function store(HostelCollectionStoreRequest $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_collection_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        try {
            $collection = $this->hostelCollectionService->createCollection($request->validated());

            Alert::toast('Hostel fee collected successfully.', 'success');

            return redirect()->route('hostel-collections.invoice', $collection->id);
        } catch (\Throwable $e) {
            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function invoice(int $id): Factory|View
    {
        $collection = HostelCollection::with(['student.user', 'roomMember.room', 'details'])->findOrFail($id);

        return view('hostel::hostel_collection.invoice', compact('collection'));
    }
}
