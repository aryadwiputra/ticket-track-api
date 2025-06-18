<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'created_by_user_id',
        'assigned_to_user_id',
        'completed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => \App\Enums\Tickets\TicketStatusEnum::class, // Assuming you might use enums
        'priority' => \App\Enums\Tickets\TicketPriorityEnum::class, // Assuming you might use enums
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who created the ticket.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the user who is assigned to the ticket.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Configure the activity log for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Log all fillable attributes
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if no attributes changed
            ->setDescriptionForEvent(fn(string $eventName) => "Ticket {$this->title} has been {$eventName}")
            ->useLogName('ticket'); // Use 'ticket' as the log name
    }
}