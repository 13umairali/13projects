<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JobsListsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $jobs;

    public function __construct($jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->jobs;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            trans('update.job'),
            trans('update.employer'),
            trans('update.employment_type'),
            trans('update.experience_level'),
            trans('update.work_arrangement'),
            trans('update.salary'),
            trans('update.apply_requests'),
            trans('admin/main.created_at'),
            trans('update.expiry_end_date'),
            trans('admin/main.status'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($job): array
    {
        $salaryText = '-';

        if (in_array($job->payment_type, ['fixed_amount', 'range'])) {
            if ($job->payment_type == "fixed_amount") {
                $salaryText = handlePrice($job->fixed_salary);
            } else {
                $salaryText = handlePrice($job->min_salary) . " - " . handlePrice($job->max_salary);
            }

            $salaryText .= "/" . trans("update.{$job->payment_period}");
        } else {
            $salaryText = trans('update.negotiable');
        }

        $expireDate = $job->getExpiryDate();

        if(!empty($expireDate) and $expireDate < time()) {
            $status = trans('update.expired');
        } else {
            $status = trans("update.{$job->status}");
        }

        return [
            $job->title,
            $job->creator->full_name,
            !empty($job->employmentType) ? $job->employmentType->title : '-',
            !empty($job->experienceLevel) ? $job->experienceLevel->title : '-',
            trans("update.{$job->work_arrangement}"),
            $salaryText,
            $job->job_requests_count,
            dateTimeFormat($job->created_at, 'j M Y H:i'),
            !empty($expireDate) ? dateTimeFormat($expireDate, 'j M Y H:i') : '-',
            $status,
        ];
    }
}
