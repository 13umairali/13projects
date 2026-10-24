<?php

namespace Modules\FormBuilder\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\ApplicantNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\FormBuilder\Models\DynamicForm;
use Modules\FormBuilder\Models\DynamicFormField;
use Modules\FormBuilder\Models\DynamicFormSubmission;
use RealRashid\SweetAlert\Facades\Alert;

class FormBuilderController extends Controller
{
    public function __construct(
        private readonly ApplicantNotificationService $notifier,
    ) {}

    /**
     * List every form belonging to the logged-in institute.
     */
    public function index()
    {
        $forms = DynamicForm::where('institute_id', get_institute_id())
            ->withCount('submissions')
            ->latest()
            ->paginate(15);

        return view('formbuilder::admin.index', compact('forms'));
    }

    public function create()
    {
        $fieldTypes = DynamicFormField::TYPES;

        return view('formbuilder::admin.builder', [
            'form' => new DynamicForm(),
            'fields' => collect(),
            'fieldTypes' => $fieldTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMeta($request);

        $form = DynamicForm::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'slug' => $this->uniqueSlug($validated['name']),
            'created_by' => auth()->id(),
        ]);

        $this->syncFields($form, $request->input('fields_json'));

        Alert::success('Success', 'Form created successfully.');

        return redirect()->route('formbuilder.forms.edit', $form->id);
    }

    public function edit(int $id)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $fields = $form->fields;
        $fieldTypes = DynamicFormField::TYPES;

        return view('formbuilder::admin.builder', compact('form', 'fields', 'fieldTypes'));
    }

    public function update(Request $request, int $id)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);

        $validated = $this->validateMeta($request);

        // Re-slugging on every rename would break links already shared/printed
        // for this form, so the slug is only (re)computed once, at creation time.
        $form->update($validated);

        $this->syncFields($form, $request->input('fields_json'));

        Alert::success('Success', 'Form updated successfully.');

        return redirect()->route('formbuilder.forms.edit', $form->id);
    }

    public function destroy(int $id)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $form->delete();

        Alert::success('Success', 'Form deleted successfully.');

        return redirect()->route('formbuilder.forms.index');
    }

    public function toggleStatus(int $id)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $form->status = $form->isPublished() ? DynamicForm::STATUS_DRAFT : DynamicForm::STATUS_PUBLISHED;
        $form->save();

        Alert::success('Success', $form->isPublished() ? 'Form published.' : 'Form moved back to draft.');

        return back();
    }

    public function toggleHomepage(int $id)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $form->show_on_homepage = ! $form->show_on_homepage;
        $form->save();

        Alert::success('Success', $form->show_on_homepage ? 'Now visible on the homepage.' : 'Removed from the homepage.');

        return back();
    }

    public function submissions(int $id)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $submissions = $form->submissions()->latest()->paginate(20);

        return view('formbuilder::admin.submissions', compact('form', 'submissions'));
    }

    public function showSubmission(int $id, int $submissionId)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $submission = $form->submissions()->findOrFail($submissionId);

        return view('formbuilder::admin.submission-show', compact('form', 'submission'));
    }

    /**
     * Approve a pending submission and notify the applicant, mirroring the
     * native Admission Forms' approve/reject pattern: a submission can only
     * be processed once.
     */
    public function approveSubmission(Request $request, int $id, int $submissionId)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $submission = $form->submissions()->findOrFail($submissionId);

        if (! $submission->isPending()) {
            Alert::error('Error', 'This submission was already processed.');

            return back();
        }

        $submission->update([
            'status' => DynamicFormSubmission::STATUS_APPROVED,
            'remarks' => $request->input('remarks'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->notifySubmissionStatus($form, $submission, approved: true);

        Alert::success('Success', 'Submission approved.');

        return back();
    }

    public function rejectSubmission(Request $request, int $id, int $submissionId)
    {
        $form = DynamicForm::where('institute_id', get_institute_id())->findOrFail($id);
        $submission = $form->submissions()->findOrFail($submissionId);

        if (! $submission->isPending()) {
            Alert::error('Error', 'This submission was already processed.');

            return back();
        }

        $submission->update([
            'status' => DynamicFormSubmission::STATUS_REJECTED,
            'remarks' => $request->input('remarks'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->notifySubmissionStatus($form, $submission, approved: false);

        Alert::success('Success', 'Submission rejected.');

        return back();
    }

    /**
     * Same name/email/phone detection used at submission time (see
     * PublicFormController::notifyApplicant) — kept in sync deliberately so
     * both notification points look for contact info the same way.
     */
    private function notifySubmissionStatus(DynamicForm $form, DynamicFormSubmission $submission, bool $approved): void
    {
        $email = null;
        $phone = null;
        $name = null;
        $data = $submission->data ?? [];

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
            return;
        }

        $statusWord = $approved ? 'approved' : 'not approved at this time';
        $lines = [
            "Your submission for \"{$form->name}\" has been {$statusWord}.",
        ];
        if ($submission->remarks) {
            $lines[] = 'Note from the reviewer: '.$submission->remarks;
        }

        $this->notifier->notify(
            instituteId: $form->institute_id,
            branchId: $form->branch_id,
            name: $name ?: 'there',
            email: $email,
            phone: $phone,
            subject: ($approved ? 'Your submission was approved — ' : 'Update on your submission — ').$form->name,
            lines: $lines,
            smsMessage: "Your submission for \"{$form->name}\" has been {$statusWord}.",
            whatsappEvent: $approved ? 'form_approved' : 'form_rejected',
            whatsappParams: ['form_name' => $form->name, 'applicant_name' => $name ?: 'there', 'remarks' => $submission->remarks ?: ''],
            referenceType: 'form_builder_submission_status',
            referenceId: $submission->id,
            schoolName: \Modules\Authentication\Models\Institute::find($form->institute_id)?->name,
        );
    }

    /**
     * Validate the form's own metadata (everything except the field list).
     */
    private function validateMeta(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'submit_button_text' => ['nullable', 'string', 'max:100'],
            'success_message' => ['nullable', 'string'],
            'notify_email' => ['nullable', 'email'],
            'icon' => ['nullable', 'string', 'max:100'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
        ]) + [
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'status' => $request->boolean('status') ? DynamicForm::STATUS_PUBLISHED : DynamicForm::STATUS_DRAFT,
        ];
    }

    /**
     * Replace the form's field set from the builder's serialized JSON payload.
     * Fields are always managed together as a batch from the builder UI, so a
     * delete-and-recreate is simpler and safer than diffing individual rows.
     */
    private function syncFields(DynamicForm $form, ?string $fieldsJson): void
    {
        $fields = json_decode($fieldsJson ?? '[]', true) ?: [];

        $form->fields()->delete();

        foreach ($fields as $index => $field) {
            if (empty($field['label'])) {
                continue;
            }

            $key = ! empty($field['field_key'])
                ? Str::slug($field['field_key'], '_')
                : Str::slug($field['label'], '_');

            $options = null;
            if (in_array($field['type'], DynamicFormField::CHOICE_TYPES, true) && ! empty($field['options'])) {
                $options = array_values(array_filter(array_map('trim', explode(',', $field['options']))));
            }

            DynamicFormField::create([
                'form_id' => $form->id,
                'label' => $field['label'],
                'field_key' => $key ?: 'field_'.$index,
                'type' => $field['type'] ?? 'text',
                'placeholder' => $field['placeholder'] ?? null,
                'options' => $options,
                'is_required' => ! empty($field['is_required']),
                'width' => $field['width'] ?? 'full',
                'sort_order' => $index,
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'form';
        $slug = $base;
        $i = 1;

        while (DynamicForm::where('institute_id', get_institute_id())->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
