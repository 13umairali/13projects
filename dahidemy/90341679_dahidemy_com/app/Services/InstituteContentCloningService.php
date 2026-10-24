<?php

namespace App\Services;

use Modules\Authentication\Models\Institute;
use Modules\Frontend\Models\AboutUs;
use Modules\Frontend\Models\AcademicImage;
use Modules\Frontend\Models\Banner;
use Modules\Frontend\Models\FaqQuestion;
use Modules\Frontend\Models\MobileAppSection;
use Modules\Frontend\Models\OurHistory;
use Modules\Frontend\Models\Policy;
use Modules\Frontend\Models\ReadyToJoinUs;
use Modules\Frontend\Models\Testimonial;
use Modules\Frontend\Models\WhyChooseUs;

class InstituteContentCloningService
{
    /**
     * The institute whose CMS content (About Us, Why Choose Us,
     * Testimonials, FAQs, Gallery, Mobile App section, banners, policies,
     * etc.) is used as the starting template for every newly created
     * institute, so a fresh institute doesn't start on a completely blank
     * homepage. Change this if a different institute should become the
     * template later.
     */
    private const TEMPLATE_DOMAIN = '90341679.dahidemy.com';

    /**
     * Copies the template institute's homepage content onto a brand-new
     * institute. Safe to call even if the template is missing (does
     * nothing) — a new institute must never fail to be created just
     * because this convenience step couldn't run. Whatever is copied is
     * fully independent afterward: the new institute's admin edits their
     * own rows without touching the template's.
     */
    public function cloneDefaultContentTo(Institute $newInstitute, ?int $branchId): void
    {
        $template = Institute::where('domain', self::TEMPLATE_DOMAIN)->first();

        if (! $template || $template->id === $newInstitute->id) {
            return;
        }

        try {
            $this->cloneAboutUs($template->id, $newInstitute->id, $branchId);
            $this->cloneOurHistory($template->id, $newInstitute->id, $branchId);
            $this->cloneMany(WhyChooseUs::class, $template->id, $newInstitute->id, $branchId);
            $this->cloneMany(Testimonial::class, $template->id, $newInstitute->id, $branchId);
            $this->cloneMany(FaqQuestion::class, $template->id, $newInstitute->id, $branchId);
            $this->cloneMany(MobileAppSection::class, $template->id, $newInstitute->id, $branchId);
            $this->cloneMany(ReadyToJoinUs::class, $template->id, $newInstitute->id, $branchId);
            $this->cloneAcademicImages($template->id, $newInstitute->id);
            $this->cloneBanners($template->id, $newInstitute->id);
            $this->clonePolicies($template->id, $newInstitute->id, $branchId);
        } catch (\Throwable $e) {
            // Deliberately swallowed: this is a convenience default, not a
            // requirement. A failure here should never prevent the
            // institute itself from having been created successfully.
            report($e);
        }
    }

    private function cloneAboutUs(int $templateId, int $newId, ?int $branchId): void
    {
        $row = AboutUs::where('institute_id', $templateId)->first();
        if (! $row) {
            return;
        }

        AboutUs::create([
            'institute_id' => $newId,
            'branch_id' => $branchId,
            'title' => $row->title,
            'description' => $row->description,
            'image' => $row->image,
            'status' => $row->status,
        ]);
    }

    private function cloneOurHistory(int $templateId, int $newId, ?int $branchId): void
    {
        $row = OurHistory::where('institute_id', $templateId)->first();
        if (! $row) {
            return;
        }

        OurHistory::create([
            'institute_id' => $newId,
            'branch_id' => $branchId,
            'year' => $row->year,
            'title' => $row->title,
            'descriptions' => $row->descriptions,
            'status' => $row->status,
        ]);
    }

    private function cloneAcademicImages(int $templateId, int $newId): void
    {
        foreach (AcademicImage::where('institute_id', $templateId)->get() as $row) {
            AcademicImage::create([
                'institute_id' => $newId,
                'title' => $row->title,
                'heading' => $row->heading,
                'description' => $row->description,
                'image' => $row->image,
                'status' => $row->status,
            ]);
        }
    }

    private function cloneBanners(int $templateId, int $newId): void
    {
        foreach (Banner::where('institute_id', $templateId)->get() as $row) {
            Banner::create([
                'institute_id' => $newId,
                'title' => $row->title,
                'description' => $row->description,
                'button_name' => $row->button_name,
                'button_link' => $row->button_link,
                'image' => $row->image,
                'status' => $row->status,
            ]);
        }
    }

    private function clonePolicies(int $templateId, int $newId, ?int $branchId): void
    {
        foreach (Policy::where('institute_id', $templateId)->get() as $row) {
            Policy::create([
                'institute_id' => $newId,
                'branch_id' => $branchId,
                'type' => $row->type,
                'description' => $row->description,
                'status' => $row->status,
            ]);
        }
    }

    /**
     * Generic clone for the simpler content types whose fillable list is
     * just plain columns with no created_by/timestamp fields to worry
     * about excluding.
     */
    private function cloneMany(string $modelClass, int $templateId, int $newId, ?int $branchId): void
    {
        foreach ($modelClass::where('institute_id', $templateId)->get() as $row) {
            $data = $row->getAttributes();
            unset($data['id'], $data['created_at'], $data['updated_at']);
            $data['institute_id'] = $newId;

            if (array_key_exists('branch_id', $data)) {
                $data['branch_id'] = $branchId;
            }

            $modelClass::create($data);
        }
    }
}