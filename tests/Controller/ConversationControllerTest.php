<?php

namespace App\Test\Controller;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConversationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ConversationRepository $repository;
    private string $path = '/conversation/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Conversation::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Conversation index');

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
            'conversation[to_user]' => 'Testing',
            'conversation[from_user]' => 'Testing',
            'conversation[plant_id]' => 'Testing',
        ]);

        self::assertResponseRedirects('/conversation/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Conversation();
        $fixture->setTo_user('My Title');
        $fixture->setFrom_user('My Title');
        $fixture->setPlant_id('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Conversation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Conversation();
        $fixture->setTo_user('My Title');
        $fixture->setFrom_user('My Title');
        $fixture->setPlant_id('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'conversation[to_user]' => 'Something New',
            'conversation[from_user]' => 'Something New',
            'conversation[plant_id]' => 'Something New',
        ]);

        self::assertResponseRedirects('/conversation/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTo_user());
        self::assertSame('Something New', $fixture[0]->getFrom_user());
        self::assertSame('Something New', $fixture[0]->getPlant_id());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Conversation();
        $fixture->setTo_user('My Title');
        $fixture->setFrom_user('My Title');
        $fixture->setPlant_id('My Title');

        $this->repository->save($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/conversation/');
    }
}
