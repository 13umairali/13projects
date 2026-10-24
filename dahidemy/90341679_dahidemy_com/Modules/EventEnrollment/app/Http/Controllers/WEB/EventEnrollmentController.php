<?php

namespace Modules\EventEnrollment\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\Event;
use Modules\EventEnrollment\Models\EventEnrollment;
use Modules\EventEnrollment\Services\EventEnrollmentService;

class EventEnrollmentController extends Controller
{
    public function __construct(
        private readonly EventEnrollmentService $events,
    ) {}

    /**
     * Handles the enrollment form submission. Free events are confirmed
     * immediately; paid events stash the attendee's details in the session
     * and move on to a gateway-selection step (nothing is written to the
     * database yet for a paid enrollment until a gateway is actually chosen —
     * avoids piling up abandoned "pending" rows for visitors who never pay).
     */
    public function enroll(Request $request, int $id)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $event = Event::where('institute_id', $institute->id)->findOrFail($id);

        if (! $this->events->isEnrollmentOpen($event)) {
            return back()->with('error', 'Enrollment for this event is not currently open.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {
            return back()->withInput()->with('error', 'Please provide at least an email or a phone number so we can confirm your registration.');
        }

        if (! $event->is_paid) {
            $enrollment = $this->events->enrollFree($event, $validated);

            return redirect()->route('eventenrollment.thankyou', ['id' => $event->id, 'ticket' => $enrollment->ticket_code]);
        }

        $request->session()->put('event_checkout_'.$event->id, $validated);

        return redirect()->route('eventenrollment.checkout', ['id' => $event->id]);
    }

    public function checkout(Request $request, int $id)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $event = Event::where('institute_id', $institute->id)->where('is_paid', true)->findOrFail($id);
        $data = $request->session()->get('event_checkout_'.$event->id);

        if (! $data) {
            return redirect()->route('eventenrollment.show', $event->id)
                ->with('error', 'Your session expired — please fill in the registration form again.');
        }

        $gateways = $this->events->availableGateways($institute->id, $event->branch_id);

        return view('eventenrollment::public.checkout', compact('institute', 'event', 'data', 'gateways'));
    }

    public function pay(Request $request, int $id, string $gateway)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $event = Event::where('institute_id', $institute->id)->where('is_paid', true)->findOrFail($id);
        $data = $request->session()->pull('event_checkout_'.$event->id);

        if (! $data) {
            return redirect()->route('eventenrollment.show', $event->id)
                ->with('error', 'Your session expired — please fill in the registration form again.');
        }

        try {
            $redirectUrl = $this->events->initiatePaidCheckout($event, $data, $gateway);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('eventenrollment.checkout', $event->id)->with('error', $e->getMessage());
        }

        return redirect($redirectUrl);
    }

    public function thankYou(Request $request, int $id, string $ticket)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $event = Event::where('institute_id', $institute->id)->findOrFail($id);
        $enrollment = EventEnrollment::where('event_id', $event->id)->where('ticket_code', $ticket)->firstOrFail();

        return view('eventenrollment::public.thank-you', compact('institute', 'event', 'enrollment'));
    }
}
