<?php

namespace Modules\EventEnrollment\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\Event;
use Modules\EventEnrollment\Services\EventEnrollmentService;

class EventPageController extends Controller
{
    public function __construct(
        private readonly EventEnrollmentService $events,
    ) {}

    public function index(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $tab = $request->query('tab', 'upcoming');
        $tab = in_array($tab, ['upcoming', 'ongoing', 'past'], true) ? $tab : 'upcoming';

        $allEvents = Event::where('institute_id', $institute->id)
            ->where('is_public', true)
            ->orderBy('start_date', $tab === 'past' ? 'desc' : 'asc')
            ->get();

        $filtered = $allEvents->filter(fn (Event $event) => $this->events->status($event) === $tab)->values();

        // Precompute per-event display data so the view doesn't need the
        // service injected into it.
        $eventCards = $filtered->map(fn (Event $event) => [
            'event' => $event,
            'seatsRemaining' => $this->events->seatsRemaining($event),
            'enrollmentOpen' => $this->events->isEnrollmentOpen($event),
        ]);

        return view('eventenrollment::public.index', [
            'institute' => $institute,
            'tab' => $tab,
            'eventCards' => $eventCards,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $event = Event::where('institute_id', $institute->id)->findOrFail($id);

        return view('eventenrollment::public.show', [
            'institute' => $institute,
            'event' => $event,
            'status' => $this->events->status($event),
            'seatsRemaining' => $this->events->seatsRemaining($event),
            'enrollmentOpen' => $this->events->isEnrollmentOpen($event),
            'gateways' => $event->is_paid ? $this->events->availableGateways($institute->id, $event->branch_id) : [],
        ]);
    }
}
