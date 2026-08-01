<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
