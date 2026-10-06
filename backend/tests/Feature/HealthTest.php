<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_the_process_health_check_is_public(): void
    {
        $this->get('/up')->assertOk();
    }
}
