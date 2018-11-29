<?php

namespace App\Tests\Controller\API;

use App\Utils\WebTestCase;

class UsersTest extends WebTestCase
{
    public function testUsersGet()
    {
        $client = static::createClient();
        $client->request('GET', '/api/users');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function testUsersPost()
    {
        // 1. Create group.
        $data = [
            'name' => 'TestGroup',
        ];

        $client = static::createClient();
        $client->request('POST', '/api/groups', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);

        // 2. Create user.
        $groupId = $data['id'];
        $data = [
            'email' => 'test@gmail.com',
            'firstname' => 'TestUser',
            'lastname' => 'TestUser',
            'state' => false,
            'groups' => [
                $groupId,
            ],
        ];

        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);

        // 3. Get user back & check groups.
        $userId = $data['id'];
        $client = static::createClient();
        $client->request('GET', '/api/users/'.$userId);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($data['groups'], [$groupId]);
    }

    public function testUserGet()
    {
        // 1. Create user.
        $data = [
            'email' => static::$faker->companyEmail(),
            'firstname' => 'TestUser',
            'lastname' => 'TestUser',
            'state' => true,
        ];

        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);

        // 2. Check if user exists.
        $userId = $data['id'];
        $client = static::createClient();
        $client->request('GET', '/api/users/'.$userId);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(true, $data['state']);
    }

    public function testUserPut()
    {
        // 1. Create group.
        $data = [
            'name' => 'TestGroup2',
        ];

        $client = static::createClient();
        $client->request('POST', '/api/groups', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $groupId = $data['id'];

        // 2. Create user.
        $data = [
            'email' => static::$faker->companyEmail(),
            'firstname' => 'TestUser',
            'lastname' => 'TestUser',
            'state' => true,
        ];

        $client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);

        // 3. Update user.
        $userId = $data['id'];
        $data = [
            'firstname' => 'UpdatedName',
            'groups' => [
                $groupId,
            ],
        ];

        $client = static::createClient();
        $client->request('PUT', '/api/users/'.$userId, [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
