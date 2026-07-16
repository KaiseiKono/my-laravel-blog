<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_base()
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
    }
}
