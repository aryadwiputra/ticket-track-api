<?php

namespace App\Enums\Tickets;

enum TicketStatusEnum: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}