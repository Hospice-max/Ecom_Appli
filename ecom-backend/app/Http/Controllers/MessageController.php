<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UsersUpdated;
use App\Models\Message;
use App\Models\User;
use App\Services\UserStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    protected $userStatusService;

    public function __construct(UserStatusService $userStatusService)
    {
        $this->userStatusService = $userStatusService;
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'recipient_id' => 'required|exists:users,id',
                'content' => 'required|string|max:1000'
            ]);

            $message = Message::create([
                'sender_id' => Auth::id(),
                'recipient_id' => $request->recipient_id,
                'message' => $request->content,
                'created_at' => now()
            ]);

            // Diffuser l'événement MessageSent
            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès',
                'data' => $message->load('sender', 'recipient')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index($userId)
    {
        try {
            $messages = Message::where(function ($query) use ($userId) {
                $query->where('sender_id', Auth::id())
                      ->where('recipient_id', $userId);
            })->orWhere(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->where('recipient_id', Auth::id());
            })->with(['sender', 'recipient'])
               ->orderBy('created_at', 'desc')
               ->get();

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function users()
    {
        try {
            $users = User::where('id', '!=', Auth::id())
                         ->get();

            // Broadcast la liste mise à jour
            broadcast(new UsersUpdated(Auth::user(), $users))->toOthers();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function destroy($id)
    {
        try {
            $user = User::where('id', $id)->first();
            $user->delete();

             // Broadcast la liste mise à jour
             broadcast(new UsersUpdated(Auth::user(), $user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Compte supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus()
    {
        $this->userStatusService->updateStatus(Auth::id(), true);
        return response()->json(['status' => 'updated']);
    }

    public function disconnect()
    {
        $this->userStatusService->updateStatus(Auth::id(), false);
        return response()->json(['status' => 'updated']);
    }
}
