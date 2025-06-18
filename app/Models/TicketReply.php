<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TicketReply extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_replies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
    ];

    /**
     * Get the ticket that the reply belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who created the reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Configure the activity log for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            // Menggunakan relasi untuk mendapatkan informasi tiket dan user
            ->setDescriptionForEvent(function (string $eventName) {
                $ticketCode = $this->ticket ? $this->ticket->code : 'N/A';
                $userName = $this->user ? $this->user->name : 'Unknown User';
                return "Reply by {$userName} for ticket {$ticketCode} has been {$eventName}";
            })
            ->useLogName('ticket_reply'); // Log name khusus untuk balasan tiket
    }
}