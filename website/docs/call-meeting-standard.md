# Website calls and meetings: same rules as the app

The website follows `flutter_app/docs/call-meeting-tones.md`. One call, one set of rules, on every platform.

## Caller
| State | Status text | Sound | File |
| --- | --- | --- | --- |
| Their device is not online yet | Calling… | soft double blip, repeats | `public/sounds/dialing.wav` |
| Their device is online and ringing | Ringing… | classic ringback, 2 s on / 4 s off | `public/sounds/ringback.wav` |
| They accepted | Connecting… | silence | – |
| Media connected | 00:01 | one short confirmation | `public/sounds/connected.wav` |

The server tells the caller whether the other device is online (`peer_online` in `GET /friends/api/calls/{id}/signals`
and in the meeting `info` of a ringing call; presence privacy applies).
Rules (`public/js/call-tones.js`): dialing never replaces ringback, neither can restart after acceptance, hang-up or decline
stops everything, a browser that blocks autoplay starts the sound on the next click or key press.

## Callee
Incoming call screen = the app's `IncomingCallView`: "Incoming Audio Call…", "Incoming Video Call…" or "Incoming Meeting…",
big photo with ripples, Decline on the left, Accept on the right, "Please wait…" while answering.
The callee only plays `ringtone.wav`, never the caller's sounds.

## Reasons shown when a call ends
Declined → "Call declined". Unanswered → "No answer" (caller) / "Missed call" (callee); a caller who hangs up while it rings
is a missed call for the callee. Everything else → "Call ended". Meetings keep their own messages (removed, not admitted, locked, full).

## Design
`public/css/call-room.css` (1:1 call) and `public/css/meeting-room.css` (meetings) share one palette: navy gradient with the
blurred profile photo, brand blue for primary actions, red/green call colours, rounded glass control panel with labelled buttons,
visible focus rings, reduced-motion support. Shortcuts in the call screen: M = mute, V = camera.
