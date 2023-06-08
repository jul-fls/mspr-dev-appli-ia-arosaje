<?php

namespace App\Test\Controller;

use App\Entity\Guarding;
use App\Entity\Plant;
use App\Repository\GuardingRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Constraints\Date;

class GuardingControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private GuardingRepository $repository;
    private string $path = '/guarding/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Guarding::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Guarding index');

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
            'guarding[from_timestamp]' => 'Testing',
            'guarding[to_timestamp]' => 'Testing',
            'guarding[plant]' => 'Testing',
            'guarding[guardian]' => 'Testing',
        ]);

        self::assertResponseRedirects('/guarding/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Guarding();
        $fixture->setFromTimestamp(DateTime::createFromFormat('Y-m-d H:i:s', '2021-10-10 10:10:10'));
        $fixture->setToTimestamp(DateTime::createFromFormat('Y-m-d H:i:s', '2021-10-10 10:10:10'));

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Guarding');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Guarding();
        $fixture->setFromTimestamp(DateTime::createFromFormat('Y-m-d H:i:s', '2021-10-10 10:10:10'));
        $fixture->setToTimestamp(DateTime::createFromFormat('Y-m-d H:i:s', '2021-10-10 10:10:10'));

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'guarding[from_timestamp]' => 'Something New',
            'guarding[to_timestamp]' => 'Something New',
            'guarding[plant]' => 'Something New',
            'guarding[guardian]' => 'Something New',
        ]);

        self::assertResponseRedirects('/guarding/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getFromTimestamp());
        self::assertSame('Something New', $fixture[0]->getToTimestamp());
        self::assertSame('Something New', $fixture[0]->getPlant());
        self::assertSame('Something New', $fixture[0]->getGuardian());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Guarding();
        $fixture->setFromTimestamp(DateTime::createFromFormat('Y-m-d H:i:s', '2021-10-10 10:10:10'));
        $fixture->setToTimestamp(DateTime::createFromFormat('Y-m-d H:i:s', '2021-10-10 10:10:10'));

        $this->repository->save($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/guarding/');
    }
}
