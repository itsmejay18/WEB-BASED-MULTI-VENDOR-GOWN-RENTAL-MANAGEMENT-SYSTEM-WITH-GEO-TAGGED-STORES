<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $conversations = Conversation::query()
            ->involvingUser($request->user()->id)
            ->with(['userOne', 'userTwo', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless(
            in_array($request->user()->id, [$conversation->user_one_id, $conversation->user_two_id], true),
            403
        );

        $conversation->load(['messages.sender', 'userOne', 'userTwo', 'booking']);

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless(
            in_array($request->user()->id, [$conversation->user_one_id, $conversation->user_two_id], true),
            403
        );

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'message' => $message->load('sender'),
        ], 201);
    }
}
