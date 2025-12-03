<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BookingControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $bookingRepository;
    private string $path = '/booking/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->bookingRepository = $this->manager->getRepository(Booking::class);

        foreach ($this->bookingRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Booking index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'booking[quantity]' => 'Testing',
            'booking[status]' => 'Testing',
            'booking[total]' => 'Testing',
            'booking[createdAt]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->bookingRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Booking();
        $fixture->setQuantity('My Title');
        $fixture->setStatus('My Title');
        $fixture->setTotal('My Title');
        $fixture->setCreatedAt('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Booking');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Booking();
        $fixture->setQuantity('Value');
        $fixture->setStatus('Value');
        $fixture->setTotal('Value');
        $fixture->setCreatedAt('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'booking[quantity]' => 'Something New',
            'booking[status]' => 'Something New',
            'booking[total]' => 'Something New',
            'booking[createdAt]' => 'Something New',
        ]);

        self::assertResponseRedirects('/booking/');

        $fixture = $this->bookingRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getQuantity());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getTotal());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Booking();
        $fixture->setQuantity('Value');
        $fixture->setStatus('Value');
        $fixture->setTotal('Value');
        $fixture->setCreatedAt('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/booking/');
        self::assertSame(0, $this->bookingRepository->count([]));
    }
}
