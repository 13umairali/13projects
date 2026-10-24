<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\Contact;
use Modules\Academic\Models\Event;
use Modules\Authentication\Services\InstituteProvisioningService;
use Modules\Frontend\Http\Requests\Onboarding\OnboardingStoreRequest;
use Modules\Frontend\Models\Onboarding;
use Modules\Frontend\Models\Policy;
use Throwable;

class FrontendController extends Controller
{
    public function privacyPolicy(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $data = Policy::query()
            ->where('institute_id', optional($institute)->id)
            ->select('id', 'institute_id', 'type', 'description')
            ->whereType(1)
            ->whereNull('deleted_at')
            ->first();

        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.policy', compact('data'));
    }

    public function cookiesPolicy(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $data = Policy::query()
            ->where('institute_id', optional($institute)->id)
            ->select('id', 'institute_id', 'type', 'description')
            ->whereType(2)
            ->whereNull('deleted_at')
            ->first();

        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.policy', compact('data'));
    }

    public function termsConditions(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $data = Policy::query()
            ->where('institute_id', optional($institute)->id)
            ->select('id', 'institute_id', 'type', 'description')
            ->whereType(3)
            ->whereNull('deleted_at')
            ->first();

        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.policy', compact('data'));
    }

    public function refundPolicy(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $data = Policy::query()
            ->where('institute_id', optional($institute)->id)
            ->select('id', 'institute_id', 'type', 'description')
            ->whereType(4)
            ->whereNull('deleted_at')
            ->first();

        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.policy', compact('data'));
    }

    public function eventDetails(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $data = Event::query()
            ->where('id', (int) $request->id)
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->first();

        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.event_details', compact('data'));
    }

    public function contact()
    {
        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'email' => [
                'required',
                'email',
                'max:191',
            ],

            'phone' => [
                'required',
                'string',
                'max:50',
            ],

            'message' => [
                'required',
                'string',
            ],

        ]);

        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $branchId = $institute ? InstituteHelper::resolveBranchIdFromRequest($request) : null;

        Contact::create([
            'institute_id' => optional($institute)->id,
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'],
        ]);

        return back()->with(
            'success',
            'Your message has been sent successfully.'
        );
    }

    public function onboarding()
    {
        return view('themes.theme-one.pages.onboarding');
    }

    /**
     * Public institute sign-up.
     *
     * Provisioning is delegated to {@see InstituteProvisioningService}, which is
     * shared with the admin approval endpoint and runs in a single transaction.
     * This flow previously inlined ~280 lines of provisioning with no transaction,
     * a duplicate copy of which lived in the approval endpoint.
     */
    public function onboardingSubmit(OnboardingStoreRequest $request, InstituteProvisioningService $provisioner)
    {
        $validated = $request->validated();

        // Uploaded outside the transaction: the filesystem is not transactional,
        // so there is nothing to gain by holding the DB transaction open for it.
        $instituteLogo = $request->hasFile('institute_logo')
            ? fileUploader('institutes/', 'png', $request->file('institute_logo'))
            : null;

        $userAvatar = $request->hasFile('user_avatar')
            ? fileUploader('user_avatars/', 'png', $request->file('user_avatar'))
            : null;

        try {
            $result = DB::transaction(function () use ($validated, $instituteLogo, $userAvatar, $provisioner) {
                $onboarding = Onboarding::create([
                    'collected_data' => Onboarding::collectedDataFrom($validated),
                    'institute_logo' => $instituteLogo,
                    'user_avatar' => $userAvatar,
                    'status' => Onboarding::STATUS_PENDING,
                ]);

                return $provisioner->provisionFromOnboarding($onboarding);
            });
        } catch (Throwable $e) {
            Log::error('Institute onboarding failed', [
                'institute_email' => $validated['institute_email'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('onboarding')
                ->withInput()
                ->with('error', _lang('We could not complete your registration. Please try again or contact support.'));
        }

        // Sent only after the transaction commits — mailing credentials for a
        // tenant that was rolled back would be worse than not mailing at all.
        $provisioner->sendWelcomeMail($result, $validated['password']);

        // Flashed for exactly one redirect - the thank-you page is the only
        // place the plaintext password is ever shown again after this
        // point (it's hashed in the database from here on).
        return redirect()
            ->route('onboarding.thank-you')
            ->with('onboarding_result', [
                'institute_name' => $result['institute']->name,
                'institute_domain' => $result['institute']->domain,
                'login_email' => $result['user']->email,
                'login_password' => $validated['password'],
                'plan_name' => $result['plan']->name,
            ]);
    }

    /**
     * Shown exactly once, immediately after a successful sign-up - reads
     * from the one-time session flash set in onboardingSubmit() above.
     * Visiting this URL directly (or refreshing it) after that single
     * redirect shows nothing, by design: this page is not a page whose
     * data should be replayable or bookmarkable, since it briefly displays
     * a plaintext password.
     */
    public function onboardingThankYou()
    {
        $result = session('onboarding_result');

        if (! $result) {
            return redirect()->route('onboarding');
        }

        return view('themes.theme-one.pages.onboarding-thank-you', [
            'result' => $result,
        ]);
    }
}