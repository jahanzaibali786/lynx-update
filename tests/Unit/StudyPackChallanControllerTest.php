<?php

namespace Tests\Unit;

use App\Http\Controllers\StudyPackChallanController;
use App\Models\StudyPackChallanItems;
use PHPUnit\Framework\TestCase;

class StudyPackChallanControllerTest extends TestCase
{
    public function test_item_payment_amount_is_capped_to_remaining_payable_amount()
    {
        $item = new StudyPackChallanItems();
        $item->price = 100;
        $item->qty = 1;
        $item->discount = 0;
        $item->paid = 40;

        $this->assertSame(60.0, StudyPackChallanController::getValidItemPaymentAmount(200, $item));
        $this->assertSame(0.0, StudyPackChallanController::getValidItemPaymentAmount(-10, $item));
    }
}
