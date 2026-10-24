<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\AccountingLedgerService;
use Modules\Finance\Models\FeeMap;
use RealRashid\SweetAlert\Facades\Alert;

class FeesMappingController extends Controller
{
    public function __construct(
        private readonly AccountingLedgerService $accountingLedgerService
    ) {}

    public function index(): View|Factory
    {
        return view('finance::fees_mapping.index', [
            'fee_mapping' => FeeMap::where('type', 'fee')->latest()->get(),
            'fee_fine_mapping' => FeeMap::where('type', 'fee_fine')->latest()->get(),
            'accountingLedgers' => $this->accountingLedgerService->getAccountingLedgers(),
        ]);
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        DB::beginTransaction();

        try {
            $feeMap = FeeMap::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'fee_head_id' => $request->fee_head_id,
                'fee_sub_head_id' => $request->fee_sub_head_id,
                'ledger_id' => $request->ledger_id,
                'type' => $request->type ?? 'fee',
            ]);

            $feeSubHeadIds = $request->fee_sub_head_ids;
            $feeHeadId = $request->fee_head_id;

            if ($request->type === 'fee') {
                $attachArray = array_map(function ($subHeadId) use ($feeHeadId) {
                    return [
                        'fee_head_id' => $feeHeadId,
                        'fee_sub_head_id' => $subHeadId,
                    ];
                }, $feeSubHeadIds);
                $feeMap->feeSubHeads()->attach($attachArray);
            } elseif ($request->type === 'fee_fine') {
                $feeMap->funds()->attach($request->fund_id);
            }

            $feeMap->funds()->attach($request->fund_ids);

            DB::commit();

            Alert::toast(_lang('Fee-Map has been create successfully'), 'success');

            return redirect('fees-mapping');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('fees-mapping');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $feeMap = FeeMap::find($id);

        if ($feeMap) {
            DB::beginTransaction();

            try {
                // Detach related feeSubHeads using raw SQL
                DB::table('fee_map_fee_sub_head')->where('fee_map_id', $feeMap->id)->delete();

                // Detach related funds using raw SQL
                DB::table('fee_map_fund')->where('fee_map_id', $feeMap->id)->delete();

                // Delete the Fee-Map record using raw SQL
                DB::table('fee_maps')->where('id', $feeMap->id)->delete();

                DB::commit();

                Alert::toast(
                    _lang('Fee-Map has been delete successfully'),
                    'success'
                );

                return redirect('fees-mapping');
            } catch (\Exception $e) {
                DB::rollBack();

                Alert::error('Error', $e->getMessage());

                return redirect('fees-mapping');
            }
        } else {
            Alert::error('Error', _lang('Something went wrong. Fee-Map can not be delete.'));

            return redirect('fees-mapping');
        }
    }
}
