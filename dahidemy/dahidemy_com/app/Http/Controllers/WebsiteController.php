<?php

namespace App\Http\Controllers;

use App\Services\Api\SaasContentService;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function __construct(
        protected SaasContentService $content
    ) {}

    public function index()
    {
        return view('pages.home', [
            'banner' => $this->content->banners()->first(),
            'stats' => $this->content->stats(),
            'revenue' => $this->content->revenue(),
            'cores' => $this->content->cores(),
            'solutions' => $this->content->solutions(),
            'benefits' => $this->content->benefits(),
            'panels' => $this->content->panels(),
            'packages' => $this->content->packages(),
            'feedback' => $this->content->feedback(),
            'faqs' => $this->content->faqs(),
        ]);
    }

    public function features()
    {
        return view('pages.features', [
            'cores' => $this->content->cores(),
            'solutions' => $this->content->solutions(),
            'benefits' => $this->content->benefits(),
            'panels' => $this->content->panels(),
        ]);
    }

    public function pricing()
    {
        return view('pages.pricing', [
            'packages' => $this->content->packages(),
            'faqs' => $this->content->faqs(),
        ]);
    }

    public function faq()
    {
        return view('pages.faq', [
            'faqs' => $this->content->faqs(),
        ]);
    }

    public function about()
    {
        return view('pages.about', [
            'stats' => $this->content->stats(),
            'revenue' => $this->content->revenue(),
            'feedback' => $this->content->feedback(),
        ]);
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $response = $this->content->contact($data);

        return $response->successful()
            ? back()->with('success', 'Thanks for reaching out! We’ll get back to you shortly.')
            : back()->withInput()->with('error', $response->message ?: 'We couldn’t send your message. Please try again.');
    }

    public function policy(string $type)
    {
        $labels = ['privacy' => 'Privacy Policy', 'terms' => 'Terms & Conditions', 'cookie' => 'Cookie Policy'];
        abort_unless(array_key_exists($type, $labels), 404);

        return view('pages.policy', [
            'type' => $type,
            'title' => $labels[$type],
            'policy' => $this->content->policy($type),
        ]);
    }

    public function registerShow(Request $request)
    {
        $planId = $request->query('plan');

        return view('pages.register', [
            'selected' => $planId ? $this->content->packages()->firstWhere('id', (int) $planId) : null,
            'packages' => $this->content->packages(),
            'instituteTypes' => $this->content->instituteTypes(),
        ]);
    }

    public function registerStore(Request $request)
    {
        // Mirrors the backend OnboardingStoreRequest contract.
        $data = $request->validate([
            'plan_id' => ['required', 'integer'],
            'institute_name' => ['required', 'string', 'max:100'],
            'institute_email' => ['required', 'email', 'max:255'],
            'institute_phone' => ['required', 'string', 'max:15'],
            'institute_domain' => ['nullable', 'string', 'max:100'],
            'referral_code' => ['nullable', 'string', 'max:20'],
            'institute_type' => ['nullable', 'string', 'max:100'],
            'institute_address' => ['nullable', 'string', 'max:100'],
            'institute_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'user_name' => ['required', 'string', 'max:100'],
            'user_email' => ['required', 'email', 'max:255'],
            'user_phone' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'user_avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'terms' => ['accepted'],
        ]);

        // Attach files + the confirmation the backend also validates against.
        $payload = collect($data)->except('terms')->all();
        $payload['password_confirmation'] = $request->input('password_confirmation');

        if ($request->hasFile('institute_logo')) {
            $payload['institute_logo'] = $request->file('institute_logo');
        }
        if ($request->hasFile('user_avatar')) {
            $payload['user_avatar'] = $request->file('user_avatar');
        }

        $selectedPlan = $this->content->packages()->firstWhere('id', (int) $data['plan_id']);

        if ($selectedPlan && ! ($selectedPlan['is_free'] ?? false)) {
            return $this->registerStorePaid($data, $payload, $selectedPlan);
        }

        $response = $this->content->onboarding($payload);

        if ($response->successful()) {
            // Flashed for exactly one redirect - this is the only place the
            // plaintext password is ever shown again after this point.
            return redirect()->route('register.thank-you')
                ->with('registration_result', [
                    'institute_name' => $data['institute_name'],
                    'institute_domain' => $data['institute_domain']
                        ? $data['institute_domain'].'.dahidemy.com'
                        : null,
                    'login_email' => $data['user_email'],
                    'login_password' => $data['password'],
                ]);
        }

        return back()->withInput()->withErrors($response->validationErrors())
            ->with('error', $response->message ?: 'We couldn’t complete your registration. Please review the form and try again.');
    }

    /**
     * A paid plan does not create the institute here at all - it asks the
     * backend for a Stripe Checkout URL and sends the browser there. The
     * form's data (needed to show the Thank You page afterward) is kept in
     * the session under a plan-agnostic key until Stripe redirects back.
     */
    protected function registerStorePaid(array $data, array $payload, array $selectedPlan)
    {
        $response = $this->content->onboardingCheckout(array_merge($payload, [
            'success_url' => route('register.confirm'),
            'cancel_url' => route('register.show'),
        ]));

        if (! $response->successful()) {
            return back()->withInput()->withErrors($response->validationErrors())
                ->with('error', $response->message ?: 'We couldn’t start checkout. Please review the form and try again.');
        }

        $paymentId = $response->get('payment_id');
        $checkoutUrl = $response->get('checkout_url');

        if (! $paymentId || ! $checkoutUrl) {
            return back()->withInput()
                ->with('error', 'We couldn’t start checkout. Please try again.');
        }

        // Not flashed (flash data only survives one redirect, and Stripe's
        // hosted checkout page is an external hop) - kept in the regular
        // session, keyed by payment_id, until registerConfirm() retrieves
        // and clears it below.
        session()->put("pending_registration.{$paymentId}", [
            'institute_name' => $data['institute_name'],
            'institute_domain' => $data['institute_domain']
                ? $data['institute_domain'].'.dahidemy.com'
                : null,
            'login_email' => $data['user_email'],
            'login_password' => $data['password'],
        ]);

        return redirect()->away($checkoutUrl);
    }

    /**
     * Where Stripe sends the browser back to after payment. Verifies the
     * payment server-side via the backend (never trusts the redirect
     * alone) before showing the Thank You page.
     */
    public function registerConfirm(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $sessionId = $request->query('session_id');

        if (! $paymentId || ! $sessionId) {
            return redirect()->route('register.show')
                ->with('error', 'Missing payment confirmation details.');
        }

        $pending = session()->pull("pending_registration.{$paymentId}");
        $response = $this->content->onboardingConfirm($paymentId, $sessionId);

        if (! $response->successful() || ! $pending) {
            return redirect()->route('register.show')
                ->with('error', $response->message ?: 'We could not confirm your payment. If you were charged, please contact support.');
        }

        return redirect()->route('register.thank-you')
            ->with('registration_result', $pending);
    }

    /**
     * Shown exactly once, immediately after a successful sign-up. Visiting
     * this URL directly or refreshing it afterward redirects back to the
     * registration form instead of re-displaying anything, since this page
     * briefly shows a plaintext password and should not be bookmarkable or
     * replayable.
     */
    public function registerThankYou()
    {
        $result = session('registration_result');

        if (! $result) {
            return redirect()->route('register.show');
        }

        return view('pages.register-thank-you', [
            'result' => $result,
        ]);
    }

    public function newsletter(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:160']]);

        // No dedicated backend endpoint yet — accepted and acknowledged.
        return back()->with('newsletter_ok', true);
    }
}