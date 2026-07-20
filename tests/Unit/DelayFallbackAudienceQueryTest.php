<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class DelayFallbackAudienceQueryTest extends TestCase
{
    public function test_route_matching_is_grouped_with_notification_preference(): void
    {
        $sql = User::query()->eligibleForDelayFallback('Uttara to DSC')->toSql();

        $this->assertMatchesRegularExpression(
            '/and \(\(?not exists \(select \* from "saved_routes".*or exists \(select \* from "saved_routes"/s',
            $sql
        );
    }

    public function test_ungrouped_or_where_has_would_bypass_preference_check(): void
    {
        $like = '%uttara%';

        $brokenSql = User::query()
            ->where(function ($q) {
                $q->where('bus_delay_notifications', true)
                    ->orWhereNull('bus_delay_notifications');
            })
            ->whereDoesntHave('savedRoutes')
            ->orWhereHas('savedRoutes', function ($sq) use ($like) {
                $sq->where('title', 'like', $like);
            })
            ->toSql();

        $this->assertDoesNotMatchRegularExpression(
            '/and \(\(?not exists \(select \* from "saved_routes".*or exists \(select \* from "saved_routes"/s',
            $brokenSql
        );
    }
}
