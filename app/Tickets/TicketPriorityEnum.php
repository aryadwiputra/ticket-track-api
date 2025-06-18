<?php

namespace App\Enums\Tickets;

enum TicketPriorityEnum: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}