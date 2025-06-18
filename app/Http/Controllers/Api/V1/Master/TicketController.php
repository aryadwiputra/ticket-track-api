<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends BaseController implements HasMiddleware
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Define the middleware for the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('custom_spatie_forbidden:tickets-access', only: ['index', 'show']),
            new Middleware('custom_spatie_forbidden:tickets-create', only: ['store']),
            new Middleware('custom_spatie_forbidden:tickets-update', only: ['update']),
            new Middleware('custom_spatie_forbidden:tickets-delete', only: ['destroy']),
            new Middleware('custom_spatie_forbidden:tickets-activity-log', only: ['activityLog']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $search = $request->input('search');

            $tickets = $this->ticketService->getTickets([
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'search' => $search,
            ]);

            return $this->sendSuccess(
                Response::HTTP_OK,
                TicketResource::collection($tickets),
                'TICKETS_RETRIEVED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error("Error retrieving tickets: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_RETRIEVING_TICKETS',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        try {
            $ticket = $this->ticketService->createTicket($request->validated());

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->performedOn($ticket)
                ->withProperties(['attributes' => $request->validated()])
                // Perbarui pesan log untuk menyertakan kode tiket
                ->log('Created ticket ' . $ticket->code . ': ' . $ticket->title);

            return $this->sendSuccess(
                Response::HTTP_CREATED,
                new TicketResource($ticket),
                'TICKET_CREATED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            \Log::error("Error creating ticket: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_CREATING_TICKET',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $ticket = $this->ticketService->getTicketById($id);

            return $this->sendSuccess(
                Response::HTTP_OK,
                new TicketResource($ticket),
                'TICKET_RETRIEVED_SUCCESSFULLY'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'TICKET_NOT_FOUND',
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            \Log::error("Error retrieving ticket: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_RETRIEVING_TICKET',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, string $id)
    {
        try {
            $ticket = $this->ticketService->updateTicket($id, $request->validated());

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->performedOn($ticket)
                ->withProperties(['attributes' => $request->validated()])
                ->log('Updated ticket: ' . $ticket->title);

            return $this->sendSuccess(
                Response::HTTP_OK,
                new TicketResource($ticket),
                'TICKET_UPDATED_SUCCESSFULLY'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'TICKET_NOT_FOUND',
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            \Log::error("Error updating ticket: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_UPDATING_TICKET',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $ticket = $this->ticketService->getTicketById($id); // Dapatkan tiket sebelum dihapus untuk logging

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                // Tambahkan 'ticket_code' ke properti log
                ->withProperties(['ticket_id' => $id, 'ticket_code' => $ticket->code, 'ticket_title' => $ticket->title])
                // Perbarui pesan log untuk menyertakan kode tiket
                ->log('Deleted ticket ' . $ticket->code . ': ' . $ticket->title);

            $this->ticketService->deleteTicket($id);

            return $this->sendSuccess(
                Response::HTTP_OK,
                null,
                'TICKET_DELETED_SUCCESSFULLY'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'TICKET_NOT_FOUND',
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            \Log::error("Error deleting ticket: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_DELETING_TICKET',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Display activity logs for tickets.
     */
    public function activityLog(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $logs = Activity::where('log_name', 'ticket')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->sendSuccess(
                Response::HTTP_OK,
                $logs, // Activity model is usually fine to return directly, or you can create a resource for it
                'TICKET_ACTIVITY_LOGS_RETRIEVED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            \Log::error("Error retrieving ticket activity logs: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'ERROR_RETRIEVING_TICKET_ACTIVITY_LOGS',
                [$e->getMessage()]
            );
        }
    }
}