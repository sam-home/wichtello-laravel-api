<?php

namespace Sam\User\Tests\Feature;

use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testIndex()
    {
        $this->get('/users')->assertExactJson(['nice']);
    }
}
