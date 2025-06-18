<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
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
        'ticket',
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
     * Get the replies for the ticket.
     */
    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

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
     * The "booted" method of the model.
     * Ini akan dijalankan saat model di-boot.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Event listener yang akan dijalankan sebelum model dibuat (disimpan ke DB)
        static::creating(function ($ticket) {
            $ticket->code = self::generateUniqueTicketCode();
        });
    }

    /**
     * Generate a unique ticket code.
     * Format: TIC-YYYYMMDD-RANDOMSTRING
     *
     * @return string
     */
    protected static function generateUniqueTicketCode(): string
    {
        $prefix = 'TIC-';
        $datePart = now()->format('Ymd'); // Bagian tanggal
        $randomPartLength = 6; // Panjang string acak

        do {
            // Hasilkan string acak uppercase
            $randomPart = Str::upper(Str::random($randomPartLength));
            $code = $prefix . $datePart . '-' . $randomPart;
        } while (self::where('code', $code)->exists()); // Pastikan kode unik di database

        return $code;
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