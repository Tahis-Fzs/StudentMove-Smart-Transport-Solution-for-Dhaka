<?php

namespace App\Services;

use App\Models\InboxMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class InboxService
{
    public function push(
        User|int $user,
        string $type,
        string $title,
        string $body,
        array $meta = [],
        ?string $icon = null
    ): InboxMessage {
        $userId = $user instanceof User ? $user->id : $user;

        return InboxMessage::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'meta' => $meta ?: null,
        ]);
    }

    /** Avoid spamming the same delay for one bus within a window. */
    public function pushDelayOnce(User $user, int $busId, string $title, string $body, int $minutes = 30): ?InboxMessage
    {
        if (!($user->bus_delay_notifications ?? true)) {
            return null;
        }

        $exists = InboxMessage::query()
            ->where('user_id', $user->id)
            ->where('type', InboxMessage::TYPE_DELAY)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->where('meta->bus_id', $busId)
            ->exists();

        if ($exists) {
            return null;
        }

        return $this->push($user, InboxMessage::TYPE_DELAY, $title, $body, [
            'bus_id' => $busId,
        ]);
    }

    public function pushArrival(User $user, string $title, string $body, array $meta = []): InboxMessage
    {
        return $this->push($user, InboxMessage::TYPE_ARRIVAL, $title, $body, $meta, 'bi-geo-alt');
    }

    public function pushBooking(User $user, string $title, string $body, array $meta = []): InboxMessage
    {
        return $this->push($user, InboxMessage::TYPE_BOOKING, $title, $body, $meta);
    }

    /** Fan-out a public announcement to matching users. */
    public function fanOutAnnouncement(Notification $announcement): int
    {
        if (!$announcement->is_active || !$announcement->isWithinSchedule()) {
            return 0;
        }

        $count = 0;
        User::query()->orderBy('id')->chunkById(100, function (Collection $users) use ($announcement, &$count) {
            foreach ($users as $user) {
                if (!$announcement->matchesUser($user)) {
                    continue;
                }

                $this->push(
                    $user,
                    InboxMessage::TYPE_ANNOUNCEMENT,
                    $announcement->title ?: 'Announcement',
                    $announcement->message,
                    [
                        'announcement_id' => $announcement->id,
                        'audience' => $announcement->audience,
                    ],
                    $announcement->icon
                );
                $count++;
            }
        });

        return $count;
    }
}
