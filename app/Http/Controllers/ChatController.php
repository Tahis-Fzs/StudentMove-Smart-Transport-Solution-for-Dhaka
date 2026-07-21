<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(protected AiService $aiService)
    {
    }

    public function index(Request $request): View
    {
        $channel = $request->query('channel', ChatThread::TYPE_ASSISTANT);
        if (!in_array($channel, [ChatThread::TYPE_ASSISTANT, ChatThread::TYPE_SUPPORT], true)) {
            $channel = ChatThread::TYPE_ASSISTANT;
        }

        $user = Auth::user();
        $assistantThread = ChatThread::forUser($user, ChatThread::TYPE_ASSISTANT);
        $supportThread = ChatThread::forUser($user, ChatThread::TYPE_SUPPORT);
        $activeThread = $channel === ChatThread::TYPE_SUPPORT ? $supportThread : $assistantThread;

        $messages = $activeThread->messages()->get();

        return view('chat.index', [
            'channel' => $channel,
            'assistantThread' => $assistantThread,
            'supportThread' => $supportThread,
            'activeThread' => $activeThread,
            'messages' => $messages,
            'aiConfigured' => $this->aiService->isConfigured(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'channel' => ['required', 'in:assistant,support'],
        ]);

        $user = Auth::user();
        $thread = ChatThread::forUser($user, $data['channel']);

        $userMessage = $thread->messages()->create([
            'role' => ChatMessage::ROLE_USER,
            'body' => trim($data['message']),
        ]);

        $thread->update(['last_message_at' => now()]);

        if ($data['channel'] === ChatThread::TYPE_SUPPORT) {
            return response()->json([
                'ok' => true,
                'messages' => [$this->serializeMessage($userMessage)],
            ]);
        }

        $history = $thread->messages()
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        $aiMessages = $history->map(fn (ChatMessage $m) => [
            'role' => $m->role === ChatMessage::ROLE_USER ? 'user' : 'assistant',
            'content' => $m->body,
        ])->all();

        try {
            $reply = $this->aiService->generateText($aiMessages);
            $offline = !$this->aiService->isConfigured();
        } catch (\Throwable $e) {
            report($e);

            $reply = trim($this->aiService->localFallback($aiMessages));
            if ($reply === '') {
                return response()->json([
                    'error' => 'AI request failed. Please try again later.',
                    'messages' => [$this->serializeMessage($userMessage)],
                ], 500);
            }

            $offline = true;
        }

        $assistantMessage = $thread->messages()->create([
            'role' => ChatMessage::ROLE_ASSISTANT,
            'body' => $reply,
            'meta' => ['offline' => $offline],
        ]);

        $thread->update(['last_message_at' => now()]);

        return response()->json([
            'ok' => true,
            'offline' => $offline,
            'messages' => [
                $this->serializeMessage($userMessage),
                $this->serializeMessage($assistantMessage),
            ],
        ]);
    }

    /** Poll for new messages (support replies from admin). */
    public function poll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channel' => ['required', 'in:assistant,support'],
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $thread = ChatThread::forUser(Auth::user(), $data['channel']);
        $query = $thread->messages()->orderBy('id');

        if (!empty($data['after_id'])) {
            $query->where('id', '>', (int) $data['after_id']);
        }

        $messages = $query->get()->map(fn (ChatMessage $m) => $this->serializeMessage($m));

        return response()->json([
            'ok' => true,
            'messages' => $messages,
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channel' => ['required', 'in:assistant'],
        ]);

        $thread = ChatThread::forUser(Auth::user(), $data['channel']);
        $thread->messages()->delete();
        $thread->update(['last_message_at' => null]);

        return response()->json(['ok' => true]);
    }

    protected function serializeMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'role' => $message->role,
            'body' => $message->body,
            'label' => $message->senderLabel(),
            'created_at' => $message->created_at?->toIso8601String(),
            'meta' => $message->meta ?? [],
        ];
    }
}
