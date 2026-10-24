<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountController extends BaseController
{
    // -------------- Get All Account ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Account::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = strtolower((string) $request->input('SortType')) === 'asc' ? 'asc' : 'desc';

        // Check If User Has Permission View  All Records
        $Accounts = Account::where('deleted_at', '=', null)

            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('account_num', 'LIKE', "%{$request->search}%")
                        ->orWhere('account_name', 'LIKE', "%{$request->search}%");
                });
            });

        $totalRows = $Accounts->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }
        $account_data = $Accounts->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        foreach ($account_data as $account) {

            $item['id'] = $account->id;
            $item['account_num'] = $account->account_num;
            $item['account_name'] = $account->account_name;
            $item['balance'] = $account->balance;
            $item['note'] = $account->note;

            $data[] = $item;
        }

        return response()->json([
            'accounts' => $data,
            'totalRows' => $totalRows,
        ]);

    }

    // -------------- Store New Account ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Account::class);

        request()->validate([
            'account_num' => 'required',
            'account_name' => 'required',
            'initial_balance' => 'required',
        ]);

        Account::create([
            'account_num' => $request['account_num'],
            'account_name' => $request['account_name'],
            'initial_balance' => $request['initial_balance'],
            'balance' => $request['initial_balance'],
            'note' => $request['note'],
        ]);

        return response()->json(['success' => true], 200);
    }

    // ------------ function show -----------\\

    public function show($id)
    {
        //

    }

    // -------------- Update Account ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Account::class);
        $Account = Account::findOrFail($id);

        request()->validate([
            'account_num' => 'required',
            'account_name' => 'required',
        ]);

        $Account->update([
            'account_num' => $request['account_num'],
            'account_name' => $request['account_name'],
            'note' => $request['note'],
        ]);

        return response()->json(['success' => true], 200);

    }

    // -------------- Delete Account ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Account::class);
        $Account = Account::findOrFail($id);

        $Account->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true], 200);
    }

    // -------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Account::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $account_id) {
            $Account = Account::findOrFail($account_id);

            $Account->update([
                'deleted_at' => Carbon::now(),
            ]);
        }

        return response()->json(['success' => true], 200);
    }

    // -------------- Account statement (all-time ledger) ---------------\\

    /**
     * GET accounts/{id}/statement — every movement that ever touched the
     * account, as a bank-style statement: Date | Type | Reference |
     * Description | Debit (out) | Credit (in) | Balance, with totals.
     *
     * The rows are rebuilt from the tables whose controllers move
     * `accounts.balance` (sale/purchase/return payments, expenses, deposits,
     * transfers, payroll, service-job payments, client/supplier opening-balance
     * payments), so the running balance is derived from the same records and
     * the closing figure can be checked against the stored balance.
     *
     * Query: from/to (Y-m-d, both optional — empty = all time, one side =
     * open-ended), search (reference/description), page/limit, SortType
     * (desc = newest first, the default).
     */
    public function statement(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', Account::class);
        $account = Account::whereNull('deleted_at')->findOrFail($id);
        $accountId = (int) $account->id;

        $from = $request->filled('from') ? Carbon::parse($request->from)->toDateString() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->toDateString() : null;
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $entries = [];
        $push = function ($row, string $type, string $direction, $amount, string $reference, string $description) use (&$entries) {
            $amount = round((float) $amount, 4);
            if ($amount == 0.0) {
                return;
            }
            $entries[] = [
                'date' => (string) $row->date,
                'created_at' => (string) ($row->created_at ?? ''),
                'type' => $type,
                'reference' => $reference,
                'description' => trim($description),
                'debit' => $direction === 'out' ? $amount : 0.0,
                'credit' => $direction === 'in' ? $amount : 0.0,
            ];
        };
        $join = fn (array $parts) => implode(' — ', array_values(array_filter(array_map(fn ($p) => trim((string) $p), $parts), fn ($p) => $p !== '')));

        // Sale payments (money in)
        $rows = DB::table('payment_sales as p')
            ->leftJoin('sales as s', 's.id', '=', 'p.sale_id')
            ->leftJoin('clients as c', 'c.id', '=', 's.client_id')
            ->leftJoin('payment_methods as m', 'm.id', '=', 'p.payment_method_id')
            ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
            ->select('p.date', 'p.created_at', 'p.Ref', 'p.montant', 's.Ref as doc_ref', 'c.name as party', 'm.name as method')
            ->get();
        foreach ($rows as $r) {
            $push($r, 'sale_payment', 'in', $r->montant, (string) $r->Ref, $join(['Sale '.$r->doc_ref, $r->party, $r->method]));
        }

        // Sale-return refunds (money out)
        $rows = DB::table('payment_sale_returns as p')
            ->leftJoin('sale_returns as s', 's.id', '=', 'p.sale_return_id')
            ->leftJoin('clients as c', 'c.id', '=', 's.client_id')
            ->leftJoin('payment_methods as m', 'm.id', '=', 'p.payment_method_id')
            ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
            ->select('p.date', 'p.created_at', 'p.Ref', 'p.montant', 's.Ref as doc_ref', 'c.name as party', 'm.name as method')
            ->get();
        foreach ($rows as $r) {
            $push($r, 'sale_return_payment', 'out', $r->montant, (string) $r->Ref, $join(['Sale return '.$r->doc_ref, $r->party, $r->method]));
        }

        // Purchase payments (money out)
        $rows = DB::table('payment_purchases as p')
            ->leftJoin('purchases as s', 's.id', '=', 'p.purchase_id')
            ->leftJoin('providers as c', 'c.id', '=', 's.provider_id')
            ->leftJoin('payment_methods as m', 'm.id', '=', 'p.payment_method_id')
            ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
            ->select('p.date', 'p.created_at', 'p.Ref', 'p.montant', 's.Ref as doc_ref', 'c.name as party', 'm.name as method')
            ->get();
        foreach ($rows as $r) {
            $push($r, 'purchase_payment', 'out', $r->montant, (string) $r->Ref, $join(['Purchase '.$r->doc_ref, $r->party, $r->method]));
        }

        // Purchase-return refunds received (money in)
        $rows = DB::table('payment_purchase_returns as p')
            ->leftJoin('purchase_returns as s', 's.id', '=', 'p.purchase_return_id')
            ->leftJoin('providers as c', 'c.id', '=', 's.provider_id')
            ->leftJoin('payment_methods as m', 'm.id', '=', 'p.payment_method_id')
            ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
            ->select('p.date', 'p.created_at', 'p.Ref', 'p.montant', 's.Ref as doc_ref', 'c.name as party', 'm.name as method')
            ->get();
        foreach ($rows as $r) {
            $push($r, 'purchase_return_payment', 'in', $r->montant, (string) $r->Ref, $join(['Purchase return '.$r->doc_ref, $r->party, $r->method]));
        }

        // Expenses (money out)
        $rows = DB::table('expenses as e')
            ->leftJoin('expense_categories as cat', 'cat.id', '=', 'e.expense_category_id')
            ->whereNull('e.deleted_at')->where('e.account_id', $accountId)
            ->select('e.date', 'e.created_at', 'e.Ref', 'e.amount', 'e.details', 'cat.name as category')
            ->get();
        foreach ($rows as $r) {
            $push($r, 'expense', 'out', $r->amount, (string) $r->Ref, $join(['Expense', $r->category, $r->details]));
        }

        // Deposits (money in)
        $rows = DB::table('deposits as d')
            ->leftJoin('deposit_categories as cat', 'cat.id', '=', 'd.deposit_category_id')
            ->whereNull('d.deleted_at')->where('d.account_id', $accountId)
            ->select('d.date', 'd.created_at', 'd.deposit_ref as Ref', 'd.amount', 'd.description', 'cat.title as category')
            ->get();
        foreach ($rows as $r) {
            $push($r, 'deposit', 'in', $r->amount, (string) $r->Ref, $join(['Deposit', $r->category, $r->description]));
        }

        // Transfers between accounts
        $rows = DB::table('transfer_money as t')
            ->leftJoin('accounts as fa', 'fa.id', '=', 't.from_account_id')
            ->leftJoin('accounts as ta', 'ta.id', '=', 't.to_account_id')
            ->whereNull('t.deleted_at')
            ->where(fn ($q) => $q->where('t.from_account_id', $accountId)->orWhere('t.to_account_id', $accountId))
            ->select('t.id', 't.date', 't.created_at', 't.amount', 't.from_account_id', 't.to_account_id', 'fa.account_name as from_name', 'ta.account_name as to_name')
            ->get();
        foreach ($rows as $r) {
            if ((int) $r->from_account_id === $accountId) {
                $push($r, 'transfer_out', 'out', $r->amount, 'TR-'.$r->id, 'Transfer to '.($r->to_name ?: '---'));
            }
            if ((int) $r->to_account_id === $accountId) {
                $push($r, 'transfer_in', 'in', $r->amount, 'TR-'.$r->id, 'Transfer from '.($r->from_name ?: '---'));
            }
        }

        // Payroll (money out)
        if (Schema::hasTable('payrolls')) {
            $rows = DB::table('payrolls as p')
                ->leftJoin('employees as e', 'e.id', '=', 'p.employee_id')
                ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
                ->select('p.date', 'p.created_at', 'p.Ref', 'p.amount', DB::raw("TRIM(CONCAT(COALESCE(e.firstname,''), ' ', COALESCE(e.lastname,''))) as party"))
                ->get();
            foreach ($rows as $r) {
                $push($r, 'payroll', 'out', $r->amount, (string) $r->Ref, $join(['Payroll', $r->party]));
            }
        }

        // Service-job payments (money in)
        if (Schema::hasTable('service_job_payments')) {
            $rows = DB::table('service_job_payments as p')
                ->leftJoin('service_jobs as j', 'j.id', '=', 'p.service_job_id')
                ->leftJoin('payment_methods as m', 'm.id', '=', 'p.payment_method_id')
                ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
                ->select('p.date', 'p.created_at', 'p.Ref', 'p.montant', 'j.Ref as doc_ref', 'm.name as method')
                ->get();
            foreach ($rows as $r) {
                $push($r, 'service_job_payment', 'in', $r->montant, (string) $r->Ref, $join(['Service job '.$r->doc_ref, $r->method]));
            }
        }

        // Customer opening-balance payments (money in)
        if (Schema::hasTable('client_opening_balance_payments')) {
            $rows = DB::table('client_opening_balance_payments as p')
                ->leftJoin('clients as c', 'c.id', '=', 'p.client_id')
                ->leftJoin('payment_methods as m', 'm.id', '=', 'p.payment_method_id')
                ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
                ->select('p.date', 'p.created_at', 'p.Ref', 'p.montant', 'c.name as party', 'm.name as method')
                ->get();
            foreach ($rows as $r) {
                $push($r, 'client_opening_balance', 'in', $r->montant, (string) $r->Ref, $join(['Customer opening balance', $r->party, $r->method]));
            }
        }

        // Supplier opening-balance payments (money out)
        if (Schema::hasTable('provider_opening_balance_payments')) {
            $rows = DB::table('provider_opening_balance_payments as p')
                ->leftJoin('providers as c', 'c.id', '=', 'p.provider_id')
                ->leftJoin('payment_methods as m', 'm.id', '=', 'p.payment_method_id')
                ->whereNull('p.deleted_at')->where('p.account_id', $accountId)
                ->select('p.date', 'p.created_at', 'p.Ref', 'p.montant', 'c.name as party', 'm.name as method')
                ->get();
            foreach ($rows as $r) {
                $push($r, 'provider_opening_balance', 'out', $r->montant, (string) $r->Ref, $join(['Supplier opening balance', $r->party, $r->method]));
            }
        }

        // Chronological order (date, then insertion order) for the running balance.
        usort($entries, function ($a, $b) {
            return [$a['date'], $a['created_at']] <=> [$b['date'], $b['created_at']];
        });

        $initial = (float) $account->initial_balance;
        $running = $initial;
        $opening = $initial;          // balance carried into the selected range
        $allTimeDebit = 0.0;
        $allTimeCredit = 0.0;
        $inRange = [];
        foreach ($entries as $e) {
            $running += $e['credit'] - $e['debit'];
            $allTimeDebit += $e['debit'];
            $allTimeCredit += $e['credit'];
            if ($from && $e['date'] < $from) {
                $opening = $running;
                continue;
            }
            if ($to && $e['date'] > $to) {
                continue;
            }
            $e['balance'] = round($running, 4);
            $inRange[] = $e;
        }
        $computedClosing = $running;
        $closing = count($inRange) ? end($inRange)['balance'] : $opening;

        $rangeDebit = array_sum(array_column($inRange, 'debit'));
        $rangeCredit = array_sum(array_column($inRange, 'credit'));

        // Search on reference / description / type, after the balance is fixed.
        if ($request->filled('search')) {
            $needle = mb_strtolower((string) $request->search);
            $inRange = array_values(array_filter($inRange, fn ($e) => str_contains(mb_strtolower($e['reference'].' '.$e['description'].' '.$e['type']), $needle)));
        }

        // Newest first by default (bank-statement style); asc = chronological.
        if (strtolower((string) $request->input('SortType', 'desc')) !== 'asc') {
            $inRange = array_reverse($inRange);
        }

        $totalRows = count($inRange);
        $perPage = (int) $request->input('limit', 25);
        if ($perPage <= 0 || $perPage > 100000) {
            $perPage = $totalRows ?: 25;
        }
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;
        $pageRows = array_slice($inRange, $offset, $perPage);
        // Ledger lines have no id of their own; number them for the table's row key.
        foreach ($pageRows as $i => &$line) {
            $line['id'] = $offset + $i + 1;
        }
        unset($line);

        return response()->json([
            'account' => [
                'id' => $account->id,
                'account_num' => $account->account_num,
                'account_name' => $account->account_name,
                'initial_balance' => round($initial, 4),
                'balance' => round((float) $account->balance, 4),
            ],
            'rows' => $pageRows,
            'totalRows' => $totalRows,
            'totals' => [
                'opening_balance' => round($opening, 4),
                'debit' => round($rangeDebit, 4),
                'credit' => round($rangeCredit, 4),
                'closing_balance' => round($closing, 4),
                'all_time_debit' => round($allTimeDebit, 4),
                'all_time_credit' => round($allTimeCredit, 4),
                'computed_balance' => round($computedClosing, 4),
                // Non-zero when the stored balance was moved by something the
                // ledger does not know about (manual DB edits, older bugs).
                'unexplained_difference' => round((float) $account->balance - $computedClosing, 4),
            ],
            'from' => $from,
            'to' => $to,
        ]);
    }
}
