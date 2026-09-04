<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudyPackChallanSchemaTest extends TestCase
{
    public function test_study_pack_challans_table_has_paid_date_column()
    {
        $this->assertTrue(Schema::hasColumn('study_pack_challans', 'paid_date'));
    }
}
