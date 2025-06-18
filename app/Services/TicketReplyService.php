<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketReplyService
{
  /**
   * Get a paginated list of replies for a specific ticket.
   *
   * @param Ticket $ticket
   * @param array $filters
   * @return LengthAwarePaginator
   */
  public function getRepliesForTicket(Ticket $ticket, array $filters): LengthAwarePaginator
  {
    $perPage = $filters['per_page'] ?? 15;
    $sortBy = $filters['sort_by'] ?? 'created_at';
    $sortOrder = $filters['sort_order'] ?? 'asc'; // Replies usually sorted oldest first

    // Eager load the user and their roles for the reply
    return $ticket->replies()
      ->with(['user.roles'])
      ->orderBy($sortBy, $sortOrder)
      ->paginate($perPage);
  }

  /**
   * Get a single ticket reply by ID.
   *
   * @param string $id
   * @return TicketReply
   * @throws ModelNotFoundException
   */
  public function getReplyById(string $id): TicketReply
  {
    // Eager load the user and their roles for the reply
    $reply = TicketReply::with(['user.roles'])->find($id);

    if (!$reply) {
      throw new ModelNotFoundException('Ticket reply not found.');
    }

    return $reply;
  }

  /**
   * Create a new ticket reply.
   *
   * @param Ticket $ticket
   * @param array $data
   * @return TicketReply
   */
  public function createReply(Ticket $ticket, array $data): TicketReply
  {
    // Ensure user_id is set to the authenticated user
    $data['user_id'] = auth()->id();

    // Create the reply associated with the ticket
    return $ticket->replies()->create($data);
  }

  /**
   * Update an existing ticket reply.
   *
   * @param string $id
   * @param array $data
   * @return TicketReply
   * @throws ModelNotFoundException
   */
  public function updateReply(string $id, array $data): TicketReply
  {
    $reply = $this->getReplyById($id);
    $reply->update($data);
    return $reply;
  }

  /**
   * Delete a ticket reply.
   *
   * @param string $id
   * @return bool
   * @throws ModelNotFoundException
   */
  public function deleteReply(string $id): bool
  {
    $reply = $this->getReplyById($id);
    return $reply->delete();
  }
}