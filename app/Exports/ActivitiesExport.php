<?php

namespace App\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActivitiesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}

    public function query()
    {
        $query = Activity::query()->orderBy('id');

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['ID', 'Actor Type', 'Actor ID', 'Action', 'Subject Type', 'Subject ID', 'Description', 'Created At'];
    }

    public function map($activity): array
    {
        return [
            $activity->id,
            $activity->actor_type ?? '',
            $activity->actor_id ?? '',
            $activity->action ?? '',
            $activity->subject_type ?? '',
            $activity->subject_id ?? '',
            $activity->description ?? '',
            $activity->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
