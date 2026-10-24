<?php

namespace Modules\FormBuilder\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use App\Services\ApplicantNotificationService;
use Illuminate\Http\Request;
use Modules\FormBuilder\Models\DynamicForm;
use Modules\FormBuilder\Models\DynamicFormSubmission;

class PublicFormController extends Controller
{
    public function __construct(
        private readonly ApplicantNotificationService $notifier,
    ) {}

    /**
     * Directory of every published form — this didn't exist before; only
     * individual /forms/{slug} pages did, with no way to browse what's
     * available.
     */
    public function index(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $forms = DynamicForm::where('institute_id', $institute->id)
            ->published()
            ->latest()
            ->get();

        return view('formbuilder::public.index', compact('institute', 'forms'));
    }

    public function show(Request $request, string $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);

        abort_if(! $institute, 404);

        $form = DynamicForm::where('institute_id', $institute->id)
            ->where('slug', $slug)
            ->published()
            ->with('fields')
            ->firstOrFail();

        return view('formbuilder::public.show', compact('form', 'institute'));
    }

    public function submit(Request $request, string $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);

        abort_if(! $institute, 404);

        $form = DynamicForm::where('institute_id', $institute->id)
            ->where('slug', $slug)
            ->published()
            ->with('fields')
            ->firstOrFail();

        $rules = [];
        foreach ($form->fields as $field) {
            $rules[$field->field_key] = $field->validationRules();
        }

        $validated = $request->validate($rules);

        // File fields are stored on disk and represented in the submission's
        // JSON payload by their saved path, same as every other file upload
        // elsewhere in this app (fileUploader() helper).
        foreach ($form->fields as $field) {
            if ($field->type === 'file' && $request->hasFile($field->field_key)) {
                $validated[$field->field_key] = fileUploader(
                    'form_submissions/',
                    $request->file($field->field_key)->getClientOriginalExtension(),
                    $request->file($field->field_key)
                );
            }
        }

        $submission = DynamicFormSubmission::create([
            'form_id' => $form->id,
            'institute_id' => $institute->id,
            'data' => $validated,
            'ip_address' => $request->ip(),
        ]);

        if (! empty($form->notify_email)) {
            $this->notifyAdmin($form, $submission);
        }

        $this->notifyApplicant($institute, $form, $submission, $validated);

        return back()->with('success', $form->success_message ?: 'Thank you! Your submission has been received.');
    }

    /**
     * Best-effort notification email to the institute admin. A misconfigured
     * mail server should never block a legitimate submission from being saved,
     * so failures are swallowed.
     */
    private function notifyAdmin(DynamicForm $form, DynamicFormSubmission $submission): void
    {
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "New submission for \"{$form->name}\":\n\n".json_encode($submission->data, JSON_PRETTY_PRINT),
                function ($message) use ($form) {
                    $message->to($form->notify_email)
                        ->subject('New form submission: '.$form->name);
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Confirm receipt to whoever filled the form out — using whichever
     * email/phone/name fields the admin included when building the form.
     * There's no guaranteed field naming, so this looks at each field's
     * *type* (email/phone) and falls back to the first text-type field for
     * a name, rather than assuming a specific field_key exists.
     */
    private function notifyApplicant(
        \Modules\Authentication\Models\Institute $institute,
        DynamicForm $form,
        DynamicFormSubmission $submission,
        array $data,
    ): void {
        $email = null;
        $phone = null;
        $name = null;

        foreach ($form->fields as $field) {
            $value = $data[$field->field_key] ?? null;
            if (! $value || is_array($value)) {
                continue;
            }

            if ($field->type === 'email' && ! $email) {
                $email = $value;
            } elseif ($field->type === 'phone' && ! $phone) {
                $phone = $value;
            } elseif (! $name && $field->type === 'text' && str_contains(strtolower($field->label), 'name')) {
                $name = $value;
            }
        }

        if (! $email && ! $phone) {
            return; // nothing to notify — the form collected no contact info
        }

        $this->notifier->notify(
            instituteId: $institute->id,
            branchId: null,
            name: $name ?: 'there',
            email: $email,
            phone: $phone,
            subject: 'We received your submission — '.$form->name,
            lines: [
                "Thank you for submitting \"{$form->name}\". We've received your response and will get back to you soon.",
                $form->success_message ?: '',
            ],
            smsMessage: "Thank you for submitting \"{$form->name}\". We've received your response.",
            whatsappEvent: 'form_submitted',
            whatsappParams: ['form_name' => $form->name, 'applicant_name' => $name ?: 'there'],
            referenceType: 'form_builder_submission',
            referenceId: $submission->id,
            schoolName: $institute->name,
        );
    }
}
