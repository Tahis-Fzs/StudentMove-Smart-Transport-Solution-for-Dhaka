<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $threads = ChatThread::query()
            ->with(['user'])
            ->where('type', ChatThread::TYPE_SUPPORT)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.chat.index', compact('threads'));
    }

    public function show(ChatThread $thread): View
    {
        abort_unless($thread->type === ChatThread::TYPE_SUPPORT, 404);

        $thread->load(['user', 'messages']);
        $thread->update(['admin_read_at' => now()]);

        return view('admin.chat.show', compact('thread'));
    }

    public function reply(Request $request, ChatThread $thread): RedirectResponse
    {
        abort_unless($thread->type === ChatThread::TYPE_SUPPORT, 404);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $thread->messages()->create([
            'role' => ChatMessage::ROLE_ADMIN,
            'body' => trim($data['message']),
            'meta' => ['admin' => true],
        ]);

        $thread->update([
            'last_message_at' => now(),
            'admin_read_at' => now(),
            'status' => ChatThread::STATUS_OPEN,
        ]);

        return back()->with('success', 'Reply sent to student.');
    }

    public function close(ChatThread $thread): RedirectResponse
    {
        abort_unless($thread->type === ChatThread::TYPE_SUPPORT, 404);

        $thread->update(['status' => ChatThread::STATUS_CLOSED]);

        return back()->with('success', 'Support thread closed.');
    }

    public function reopen(ChatThread $thread): RedirectResponse
    {
        abort_unless($thread->type === ChatThread::TYPE_SUPPORT, 404);

        $thread->update(['status' => ChatThread::STATUS_OPEN]);

        return back()->with('success', 'Support thread reopened.');
    }
}
