<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\TicketReplies\StoreTicketReplyRequest;
use App\Http\Requests\TicketReplies\UpdateTicketReplyRequest;
use App\Http\Resources\TicketReplyResource;
use App\Models\Ticket; // Import Ticket model for route model binding
use App\Models\TicketReply; // Import TicketReply model for route model binding
use App\Services\TicketReplyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

class TicketReplyController extends BaseController implements HasMiddleware
{
    protected TicketReplyService $ticketReplyService;

    public function __construct(TicketReplyService $ticketReplyService)
    {
        $this->ticketReplyService = $ticketReplyService;
    }

    /**
     * Define the middleware for the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('custom_spatie_forbidden:ticket-replies-access', only: ['index', 'show']),
            new Middleware('custom_spatie_forbidden:ticket-replies-create', only: ['store']),
            new Middleware('custom_spatie_forbidden:ticket-replies-update', only: ['update']),
            new Middleware('custom_spatie_forbidden:ticket-replies-delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the replies for a specific ticket.
     */
    public function index(Ticket $ticket, Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'asc');

            $replies = $this->ticketReplyService->getRepliesForTicket($ticket, [
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]);

            return $this->sendSuccess(
                Response::HTTP_OK,
                TicketReplyResource::collection($replies),
                'TICKET_REPLIES_RETRIEVED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            \Log::error("Error retrieving ticket replies for ticket {$ticket->id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_RETRIEVING_TICKET_REPLIES',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Store a newly created reply for a specific ticket.
     */
    public function store(Ticket $ticket, StoreTicketReplyRequest $request)
    {
        try {
            $reply = $this->ticketReplyService->createReply($ticket, $request->validated());

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->performedOn($reply)
                ->withProperties(['attributes' => $request->validated(), 'ticket_id' => $ticket->id, 'ticket_code' => $ticket->code])
                ->log('Created reply for ticket ' . $ticket->code);

            return $this->sendSuccess(
                Response::HTTP_CREATED,
                new TicketReplyResource($reply),
                'TICKET_REPLY_CREATED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            \Log::error("Error creating ticket reply for ticket {$ticket->id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_CREATING_TICKET_REPLY',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Display the specified ticket reply.
     */
    public function show(Ticket $ticket, TicketReply $reply)
    {
        try {
            // Ensure the reply belongs to the given ticket (optional but good practice for nested resources)
            if ($reply->ticket_id !== $ticket->id) {
                throw new ModelNotFoundException('Ticket reply not found for the specified ticket.');
            }

            // The service method already eager loads user and roles
            $foundReply = $this->ticketReplyService->getReplyById($reply->id);

            return $this->sendSuccess(
                Response::HTTP_OK,
                new TicketReplyResource($foundReply),
                'TICKET_REPLY_RETRIEVED_SUCCESSFULLY'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'TICKET_REPLY_NOT_FOUND',
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            \Log::error("Error retrieving ticket reply {$reply->id} for ticket {$ticket->id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_RETRIEVING_TICKET_REPLY',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Update the specified ticket reply.
     */
    public function update(Ticket $ticket, UpdateTicketReplyRequest $request, TicketReply $reply)
    {
        try {
            // Ensure the reply belongs to the given ticket
            if ($reply->ticket_id !== $ticket->id) {
                throw new ModelNotFoundException('Ticket reply not found for the specified ticket.');
            }

            $updatedReply = $this->ticketReplyService->updateReply($reply->id, $request->validated());

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->performedOn($updatedReply)
                ->withProperties(['attributes' => $request->validated(), 'ticket_id' => $ticket->id, 'ticket_code' => $ticket->code])
                ->log('Updated reply ' . $reply->id . ' for ticket ' . $ticket->code);

            return $this->sendSuccess(
                Response::HTTP_OK,
                new TicketReplyResource($updatedReply),
                'TICKET_REPLY_UPDATED_SUCCESSFULLY'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'TICKET_REPLY_NOT_FOUND',
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            \Log::error("Error updating ticket reply {$reply->id} for ticket {$ticket->id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_UPDATING_TICKET_REPLY',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified ticket reply from storage.
     */
    public function destroy(Ticket $ticket, TicketReply $reply)
    {
        try {
            // Ensure the reply belongs to the given ticket
            if ($reply->ticket_id !== $ticket->id) {
                throw new ModelNotFoundException('Ticket reply not found for the specified ticket.');
            }

            $this->ticketReplyService->deleteReply($reply->id);

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['ticket_reply_id' => $reply->id, 'ticket_id' => $ticket->id, 'ticket_code' => $ticket->code])
                ->log('Deleted reply ' . $reply->id . ' for ticket ' . $ticket->code);

            return $this->sendSuccess(
                Response::HTTP_OK,
                null,
                'TICKET_REPLY_DELETED_SUCCESSFULLY'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'TICKET_REPLY_NOT_FOUND',
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            \Log::error("Error deleting ticket reply {$reply->id} for ticket {$ticket->id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_DELETING_TICKET_REPLY',
                [$e->getMessage()]
            );
        }
    }
}