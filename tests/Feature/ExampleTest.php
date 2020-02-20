<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * make sure all unhandled routes return 404.
     *
     * @test
     */
    public function unhandledRoutes()
    {
        $response = $this->get('/idontknow');

        $response->assertStatus(404)
                 ->assertExactJson(['message' => 'Page Not Found.']);
    }
}
