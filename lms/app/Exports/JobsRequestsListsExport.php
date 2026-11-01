<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JobsRequestsListsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $jobRequests;

    public function __construct($jobRequests)
    {
        $this->jobRequests = $jobRequests;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->jobRequests;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            trans('update.jobRequest'),
            trans('update.applicant'),
            trans('update.shared_information'),
            trans('update.request_date'),
            trans('admin/main.status'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($jobRequest): array
    {
        $job = $jobRequest->job;
        $sharedInformation = $jobRequest->getSharedInformation();

        return [
            $job->title,
            $jobRequest->user->full_name,
            (!empty($sharedInformation)) ? implode(',', $sharedInformation) : '-',
            dateTimeFormat($jobRequest->created_at, 'j M Y H:i'),
            trans("update.{$jobRequest->status}")
        ];
    }
}
