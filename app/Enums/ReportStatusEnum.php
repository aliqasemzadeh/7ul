<?php

namespace App\Enums;

enum ReportStatusEnum: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __('app.report.statuses.'.$this->value);
    }
}
