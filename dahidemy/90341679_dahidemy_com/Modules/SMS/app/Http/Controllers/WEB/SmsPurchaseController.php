<?php

namespace Modules\SMS\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\SMS\Models\SmsBalance;
use Modules\SMS\Models\SmsPurchase;
use RealRashid\SweetAlert\Facades\Alert;

class SmsPurchaseController extends Controller
{
    public function index(): Factory|View
    {
        // Was completely unscoped -- every institute could see every other
        // institute's SMS purchase history (dates, amounts, gateway used).
        $smsPurchases = SmsPurchase::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->latest()
            ->get();

        return view('sms::sms-purchase.index', compact('smsPurchases'));
    }

    public function create(): Factory|View
    {
        return view('sms::sms-purchase.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'sms_gateway' => 'required|string',
            'masking_type' => 'required|string',
            'transaction_date' => 'required|date',
            'no_of_sms' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $purchase = SmsPurchase::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'sms_gateway' => $request->sms_gateway,
                'masking_type' => $request->masking_type,
                'transaction_date' => $request->transaction_date,
                'no_of_sms' => $request->no_of_sms,
                'amount' => $request->amount,
            ]);

            if ($purchase) {
                // See the matching comment in update() -- SmsBalance needs to be
                // scoped to the current institute explicitly; it has no
                // tenant-scoping of its own.
                $balance = SmsBalance::firstOrCreate([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                ]);
                if ($balance) {
                    if ($request->masking_type == 'masking') {
                        $newBalance = $balance->masking_balance + $request->no_of_sms;
                        $balance->masking_balance = $newBalance;
                    } elseif ($request->masking_type == 'nonmasking') {
                        $newBalance = $balance->non_masking_balance + $request->no_of_sms;
                        $balance->non_masking_balance = $newBalance;
                    } else {
                        Alert::error('Error', _lang('Something went wrong.'));

                        return redirect('sms-purchase');
                    }

                    $balance->save();
                }
            }
            DB::commit();

            Alert::toast('Purchase Successfully.', 'success');

            return redirect('sms-purchase');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('sms-purchase');
        }
    }

    public function edit(int $id): Factory|View
    {
        // Was unscoped ($id alone) -- any institute could edit any other
        // institute's purchase record just by guessing/incrementing the ID
        // in the URL. Same fix applied to update() and destroy() below.
        $purchase = SmsPurchase::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->findOrFail($id);

        return view('sms::sms-purchase.edit', compact('purchase'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        // Validate the request data
        $validatedData = $request->validate([
            'sms_gateway' => 'required|string',
            'masking_type' => 'required|string',
            'transaction_date' => 'required|date',
            'no_of_sms' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        // Find the purchase record by ID, scoped to this institute -- see the
        // comment in edit() above.
        $purchase = SmsPurchase::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);
        if ($purchase) {
            // SmsBalance has no institute scoping of its own -- SmsBalance::first()
            // always returned the very first row in the whole table (in practice,
            // institute_id=1's row) regardless of which institute was actually
            // logged in, so every other institute's SMS purchases were silently
            // crediting institute 1's balance instead of their own, and institute
            // 1's balance was being drained by every other institute's edits.
            $balance = SmsBalance::firstOrCreate([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
            ]);

            $qtyDifferent = $request->no_of_sms - $purchase->no_of_sms;

            // Update balance based on masking type
            if ($request->masking_type === 'masking') {
                $balance->masking_balance += $qtyDifferent;
            } elseif ($request->masking_type === 'nonmasking') {
                $balance->non_masking_balance += $qtyDifferent;
            } else {
                Alert::error('Error', _lang('Something went wrong.'));

                return redirect('sms-purchase');
            }

            // Save the updated balance
            $balance->save();

            // Update the purchase record with validated data
            $purchase->update($validatedData);

            Alert::toast('Update Successfully.', 'success');

            return redirect('sms-purchase');
        }

        Alert::error('Error', _lang('Purchase record not found.'));

        return redirect('sms-purchase');
    }

    public function destroy(SmsPurchase $smsPurchase): Redirector|RedirectResponse
    {
        // Route-model-binding resolves $smsPurchase before this method runs,
        // with no institute scoping of its own -- same cross-tenant gap as
        // edit()/update() above, checked explicitly here instead.
        if ($smsPurchase->institute_id !== get_institute_id() || $smsPurchase->branch_id !== get_branch_id()) {
            abort(404);
        }

        $smsPurchase->delete();

        Alert::toast('Delete Successfully.', 'success');

        return redirect('sms-purchase');
    }
}
