<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Middleware d'authentification
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string'
            ]);

            $message = Message::create([
                'user_id' => Auth::id(),
                'content' => $validated['content'],
                'time' => now()
            ]);

            // Broadcast le message à tous les utilisateurs connectés sauf l'émetteur
            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès',
                'data' => $message->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $messages = Message::with('user')->latest()->get();

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
}
