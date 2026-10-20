# Calls and meetings: screens and tones

## Screens
Calls and meetings share one look (`lib/core/wa_call_view.dart`): dark backdrop, name + status on top,
ripples around the picture (or a meeting icon) until connected, rounded control panel at the bottom.

| Moment | Calls (1:1) | Meetings |
| --- | --- | --- |
| Incoming | `IncomingCallView` ("Incoming Audio/Video Call…") | same view, "Incoming Meeting…" |
| Before joining | – | `WaCallView` with own camera, mic/camera toggles, green **Join now** |
| Dialing / connecting | "Calling…" → "Ringing…" → "Connecting…" | "Connecting…" |
| Waiting room / error / ended | – | same view with the message as status |
| Alone in the room | – | own camera, "Waiting for others to join…", add people |
| Connected | timer, remote video, draggable self view | participant grid, same top bar and control panel |

## Tones (caller side)
Three different sounds, one set for audio calls, video calls and meetings:

| State | Status text | Tone | File |
| --- | --- | --- | --- |
| Other person not connected yet | Calling… | soft double blip, repeats | `dialing.wav` |
| Their device is ringing | Ringing… | classic ringback, 2 s on / 4 s off | `ringback.wav` |
| They accepted | Connecting… | silence | – |
| Media connected | 00:01 | one short confirmation | `connected.wav` |

Rules: `dialing` never replaces `ringback`; after acceptance neither can restart; hang-up or decline stops
everything. The callee never plays these: their phone uses the system ringtone and vibration.
Reasons shown when the call ends: declined → "Call declined", unanswered → "No answer" (caller) /
"Missed call" (callee), otherwise "Call ended".
