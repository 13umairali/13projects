<?php

namespace Modules\SMS\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Modules\Academic\Models\SmsLog;
use Modules\Academic\Services\UserService;
use Modules\SMS\Services\SmsTemplateService;
use RealRashid\SweetAlert\Facades\Alert;

class SmsController extends Controller
{
    public function __construct(
        private readonly SmsTemplateService $smsTemplateService,
        private readonly UserService $userService,
    ) {}

    public function index(): Factory|View
    {
        return view('sms::sms_compose.index', [
            'smsTemplates' => $this->smsTemplateService->getSmsTemplates(),
        ]);
    }

    public function send(Request $request): Redirector|RedirectResponse
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $smsType = get_option('sms_type');
        $body = strip_tags($request->input('body'));
        $length = $this->calculateSmsLength($body);

        try {
            if ($request->input('users') != '') {
                foreach ($request->input('users') as $user_id => $mobile_number) {
                    $log = new SmsLog;
                    $log->receiver = $mobile_number;
                    $log->message = $body;
                    $log->user_id = $user_id ?? 0;
                    $log->user_type = $request->user_type;
                    $log->sender_id = Auth::user()->id;
                    $log->status = 0;
                    $log->save();
                }
            } elseif ($request->input('individual_number') != '') {
                $log = new SmsLog;
                $log->receiver = $request->individual_number;
                $log->message = $body;
                $log->user_id = $user_id ?? 0;
                $log->user_type = 'Individual';
                $log->sender_id = Auth::user()->id;
                $log->status = 0;
                $log->save();
            } else {
                Alert::error('Error', _lang('Invalid mobile number Or Illegal Operation.'));

                return redirect('sms-compose');
            }

            Alert::toast('Message Send Successfully.', 'success');

            return redirect('sms-compose');
        } catch (Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect('sms-compose');
        }
    }

    public function sentSMSReport(Request $request): Factory|View
    {
        $smsReports = [];
        $requestFromDate = $request->from;
        $requestToDate = $request->to;

        $fromDate = Carbon::parse($requestFromDate)->startOfDay();
        $toDate = Carbon::parse($requestToDate)->endOfDay();

        if ($requestToDate && $requestToDate) {
            $smsReports = SmsLog::where('status', 1)->whereBetween('created_at', [$fromDate, $toDate])
                ->with('user')
                ->get();
        }

        return view('sms::sms_compose.reports.sms', compact('smsReports', 'requestFromDate', 'requestToDate'));
    }

    public function get_users($user_type = ''): Factory|Redirector|RedirectResponse|View
    {
        if ($user_type == '') {
            Alert::error('Error', _lang('Something went wrong. User not found.'));

            return redirect('sms-template');
        }

        $users = $this->userService->getUsersByExistType($user_type);

        return view('sms::sms_compose.others-filter-data', compact('users'));
    }

    public function calculateSmsLength($message)
    {
        // Count the number of characters in the message
        $messageLength = strlen($message);

        // Check if the message length is within the SMS limit
        if ($messageLength <= 160) {
            // The message fits in a single SMS
            return 1;
        } else {
            // The message needs to be split into multiple SMS messages
            $numberOfSms = ceil($messageLength / 160);

            return $numberOfSms;
        }
    }
}
