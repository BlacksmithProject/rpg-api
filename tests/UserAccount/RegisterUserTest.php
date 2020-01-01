<?php declare(strict_types=1);

namespace App\Tests\UserAccount;

use App\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Response;

final class RegisterUserTest extends AbstractTest
{
    public function testUserCanRegister()
    {
        $this->client->request('POST', '/api/v1/fr/users', [], [], [], json_encode([
            'name' => 'John Doe',
            'password' => 'WhatASuperSecretPassword !'
        ]));

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertSame('John Doe', $content['name']);
        $this->assertArrayHasKey('authenticationToken', $content);
        $this->assertArrayHasKey('value', $content['authenticationToken']);
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCannotRegisterWithUsedName()
    {
        $this->client->request('POST', '/api/v1/fr/users', [], [], [], json_encode([
            'name' => 'John Doe',
            'password' => 'WhatASuperSecretPassword !'
        ]));
        $this->client->request('POST', '/api/v1/fr/users', [], [], [], json_encode([
            'name' => 'John Doe',
            'password' => 'WhatASuperSecretPassword !'
        ]));

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $content);
        $this->assertSame(["L'email ou le nom d'utilisateur sont déjà utilisés"], $content['errors']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCannotRegisterWithBlankName()
    {
        $this->client->request('POST', '/api/v1/fr/users', [], [], [], json_encode([
            'name' => '',
            'password' => 'WhatASuperSecretPassword !'
        ]));

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $content);
        $this->assertSame(["Le nom d'utilisateur doit être renseigné"], $content['errors']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCannotRegisterWithBlankPassword()
    {
        $this->client->request('POST', '/api/v1/fr/users', [], [], [], json_encode([
            'name' => 'John Doe',
            'password' => ''
        ]));

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $content);
        $this->assertSame(["Le mot de passe doit être renseigné"], $content['errors']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCannotRegisterWithMissingName()
    {
        $this->client->request('POST', '/api/v1/fr/users', [], [], [], json_encode([
            'password' => 'WhatASuperSecretPassword !'
        ]));

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $content);
        $this->assertSame(["Le nom d'utilisateur est obligatoire"], $content['errors']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCannotRegisterWithMissingPassword()
    {
        $this->client->request('POST', '/api/v1/fr/users', [], [], [], json_encode([
            'name' => 'John Doe'
        ]));

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $content);
        $this->assertSame(["Le mot de passe est obligatoire"], $content['errors']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }
}
