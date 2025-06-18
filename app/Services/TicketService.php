<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketService
{
  /**
   * Get a paginated list of tickets.
   *
   * @param array $filters
   * @return LengthAwarePaginator
   */
  public function getTickets(array $filters): LengthAwarePaginator
  {
    $perPage = $filters['per_page'] ?? 15;
    $sortBy = $filters['sort_by'] ?? 'created_at';
    $sortOrder = $filters['sort_order'] ?? 'desc';
    $search = $filters['search'] ?? null;

    $query = Ticket::query();

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', '%' . $search . '%')
          ->orWhere('description', 'like', '%' . $search . '%');
      });
    }

    // Eager load relationships to avoid N+1 problem in resource
    $query->with(['createdBy', 'assignedTo']);

    return $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
  }

  /**
   * Get a single ticket by ID.
   *
   * @param string $id
   * @return Ticket
   * @throws ModelNotFoundException
   */
  public function getTicketById(string $id): Ticket
  {
    // Eager load relationships
    $ticket = Ticket::with(['createdBy', 'assignedTo'])->find($id);

    if (!$ticket) {
      throw new ModelNotFoundException('Ticket not found.');
    }

    return $ticket;
  }

  /**
   * Create a new ticket.
   *
   * @param array $data
   * @return Ticket
   */
  public function createTicket(array $data): Ticket
  {
    // Ensure created_by_user_id is set to the authenticated user
    $data['created_by_user_id'] = auth()->id();

    return Ticket::create($data);
  }

  /**
   * Update an existing ticket.
   *
   * @param string $id
   * @param array $data
   * @return Ticket
   * @throws ModelNotFoundException
   */
  public function updateTicket(string $id, array $data): Ticket
  {
    $ticket = $this->getTicketById($id); // Re-use getTicketById to ensure existence and eager loading
    $ticket->update($data);
    return $ticket;
  }

  /**
   * Delete a ticket.
   *
   * @param string $id
   * @return bool
   * @throws ModelNotFoundException
   */
  public function deleteTicket(string $id): bool
  {
    $ticket = $this->getTicketById($id); // Re-use getTicketById to ensure existence
    return $ticket->delete();
  }
}