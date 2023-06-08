<?php

namespace App\Test\Controller;

use App\Entity\Plant;
use App\Repository\PlantRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlantControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private PlantRepository $repository;
    private string $path = '/plant/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Plant::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Plant index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'plant[plant_name]' => 'Testing',
            'plant[scientific_name]' => 'Testing',
            'plant[family_name]' => 'Testing',
            'plant[image]' => 'Testing',
            'plant[age]' => 'Testing',
            'plant[environment]' => 'Testing',
            'plant[gbif_id]' => 'Testing',
            'plant[is_published]' => 'Testing',
            'plant[owner]' => 'Testing',
        ]);

        self::assertResponseRedirects('/plant/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Plant();
        $fixture->setPlantName('My Title');
        $fixture->setScientificName('My Title');
        $fixture->setFamilyName('My Title');
        $fixture->setImage('My Title');
        $fixture->setBirth('My Title');
        $fixture->setEnvironment('My Title');
        $fixture->setGbifId('My Title');
        $fixture->setIsPublished('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Plant');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Plant();
        $fixture->setPlantName('My Title');
        $fixture->setScientificName('My Title');
        $fixture->setFamilyName('My Title');
        $fixture->setImage('My Title');
        $fixture->setBirth('My Title');
        $fixture->setEnvironment('My Title');
        $fixture->setGbifId('My Title');
        $fixture->setIsPublished('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'plant[plant_name]' => 'Something New',
            'plant[scientific_name]' => 'Something New',
            'plant[family_name]' => 'Something New',
            'plant[image]' => 'Something New',
            'plant[age]' => 'Something New',
            'plant[environment]' => 'Something New',
            'plant[gbif_id]' => 'Something New',
            'plant[is_published]' => 'Something New',
            'plant[owner]' => 'Something New',
        ]);

        self::assertResponseRedirects('/plant/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getPlantName());
        self::assertSame('Something New', $fixture[0]->getScientificName());
        self::assertSame('Something New', $fixture[0]->getFamilyName());
        self::assertSame('Something New', $fixture[0]->getImage());
        self::assertSame('Something New', $fixture[0]->getBirth());
        self::assertSame('Something New', $fixture[0]->getGbifId());
        self::assertSame('Something New', $fixture[0]->getOwner());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Plant();
        $fixture->setPlantName('My Title');
        $fixture->setScientificName('My Title');
        $fixture->setFamilyName('My Title');
        $fixture->setImage('My Title');
        $fixture->setBirth('My Title');
        $fixture->setEnvironment('My Title');
        $fixture->setGbifId('My Title');
        $fixture->setIsPublished('My Title');

        $this->repository->save($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/plant/');
    }
}
