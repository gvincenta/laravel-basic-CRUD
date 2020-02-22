<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class UnhandledRouteTest
 * @package Tests\Feature
 * make sure all unhandled routes return 404.
 */
class UnhandledRouteTest extends TestCase
{
    /**  @test make sure all unhandled routes return 404. */
    public function unhandledRoutes()
    {
        $response = $this->get('/idontknow');

        $response->assertStatus(404)
                 ->assertExactJson(['message' => 'Page Not Found.']);
    }
}
