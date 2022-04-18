<?php

namespace App\Tests;

use App\Entity\Stock;
use App\Http\FakeYahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void 
    {
        // SETUP
        parent::setUp();

        $application = new Application(self::$kernel);

        $command = $application->find('app:refresh-stock-profile');

        $this->commandTester = new CommandTester($command);
    }

    /** @test */
    public function the_refresh_stock_profile_command_creates_new_records_correctly()
    {
        // SETUP
        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD","price":3295.47,"previousClose":3272.99,"priceChange":22.48}';

        // DO SOMETHING
        $this->commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US',
        ]);

        // MAKE ASSERTIONS
        $repo = $this->entityManager->getRepository(Stock::class);

        /** @var Stock $stock */
        $stock = $repo->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50, $stock->getPreviousClose());
        $this->assertGreaterThan(50, $stock->getPrice());
    }

    /** @test */
    public function the_refresh_stock_profile_command_updates_existing_records_correctly()
    {
        // SETUP
        // initialize 1 record
        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD","price":3295.47,"previousClose":3272.99,"priceChange":22.48}';

        // DO SOMETHING
        // create a new record
        $this->commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US',
        ]);
        // create a new record with the same symbol
        $this->commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US',
        ]);

        // MAKE ASSERTIONS
        // assert only 1 record was created
        $repo = $this->entityManager->getRepository(Stock::class);

        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(1, $stockRecordCount);
    }

    /** @test */
    public function the_refresh_stock_profile_command_behaves_correctly_when_a_stock_record_does_not_exist()
    {
        // SETUP
        FakeYahooFinanceApiClient::$content = '';

        // DO SOMETHING
        $commandStatus = $this->commandTester->execute([
            'symbol' => 'UnknownSymbol',
            'region' => 'US',
        ]);

        // MAKE ASSERTIONS
        $repo = $this->entityManager->getRepository(Stock::class);

        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(1, $commandStatus);
        $this->assertEquals(0, $stockRecordCount);
    }

    /** @test */
    public function non_200_status_code_responses_are_handled_correctly()
    {
        // SETUP
        FakeYahooFinanceApiClient::$statusCode = 500;
        FakeYahooFinanceApiClient::$content = 'Finance API Client Error';

        // DO SOMETHING
        $commandStatus = $this->commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US',
        ]);

        // MAKE ASSERTIONS
        $repo = $this->entityManager->getRepository(Stock::class);

        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // MAKE ASSERTIONS
        $this->assertEquals(1, $commandStatus);
        $this->assertEquals(0, $stockRecordCount);
    }
}