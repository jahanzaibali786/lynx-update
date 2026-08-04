<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StudyPackReceiptsRouteTest extends TestCase
{
    public function test_daily_studypack_receipts_route_is_registered(): void
    {
        $route = Route::getRoutes()->getByName('studypackreceipts.daily');

        $this->assertNotNull($route);
        $this->assertStringContainsString('StudyPackChallanController@dailyReceipts', $route->getAction()['controller'] ?? '');
    }
}
