<?php

namespace App\Tests\Controller\API;

use App\Utils\WebTestCase;

class GroupsTest extends WebTestCase
{
    public function testGroupsGet()
    {
        $client = static::createClient();
        $client->request('GET', '/api/groups');
		$response = $client->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertJson($response->getContent());
    }
	
	public function testGroupsPost( )
	{
		$data = [
			'name' => 'TestGroup1'
		];
		
		$client = static::createClient();
        $client->request('POST', '/api/groups', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
		$response = $client->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertJson($response->getContent());
		$data = json_decode( $response->getContent(), true );
		$this->assertArrayHasKey('id', $data);
	}
	
	public function testGroupPut( )
	{
		// 1. Create group
		$data = [
			'name' => 'TestGroup2'
		];
		
		$client = static::createClient();
        $client->request('POST', '/api/groups', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
		$response = $client->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertJson($response->getContent());
		$data = json_decode( $response->getContent(), true );
		
		// 2. Create user.
		$groupId = $data['id'];
		$data = [
			'email' => static::$faker->companyEmail(),
			'firstname' => 'TestUser',
			'lastname' => 'TestUser',
			'state' => false,
		];
		
		$client = static::createClient();
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
		$response = $client->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertJson($response->getContent());
		$data = json_decode( $response->getContent(), true );
		
		// 3. Update group
		$userId = $data['id'];
		$data = [
			'name' => 'UpdatedGroup',
			'users' => [
				$userId
			]
		];
		
		$client = static::createClient();
        $client->request('PUT', '/api/groups/'.$groupId, [], [], ['CONTENT_TYPE' => 'application/json'], $this->serialize($data));
		$response = $client->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertJson($response->getContent());
	}
}