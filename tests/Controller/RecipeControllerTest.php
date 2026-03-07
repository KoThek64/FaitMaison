<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecipeControllerTest extends WebTestCase
{
    public function testRecettes()
    {
        $client = static::createClient();
        $client->request('GET', '/recettes');
        $this->assertResponseIsSuccessful();
    }

    public function testRecetteNouvelle()
    {
        $client = static::createClient();
        $client->request('GET', '/recette/ajouter');
        $this->assertResponseRedirects('/login');
    }
}
