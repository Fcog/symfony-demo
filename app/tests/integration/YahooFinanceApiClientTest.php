<?php

namespace App\Tests\integration;

use App\Tests\DatabaseDependantTestCase;

class YahooFinanceApiClientTest extends DatabaseDependantTestCase
{
    /**
     * @test
     * @group integration
     */
    public function the_yahoo_finance_api_client_returns_the_correct_data()
    {
        // Setup
        $yahooFinanceApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        // Do something
        $response = $yahooFinanceApiClient->fetchStockProfile('AMZN', 'US');

        $stockProfile = json_decode($response->getContent());

        // Make assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $stockProfile->region);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('USD', $stockProfile->currency);
        $this->assertIsNumeric($stockProfile->price);
        $this->assertIsNumeric($stockProfile->previousClose);
        $this->assertIsNumeric($stockProfile->priceChange);
    }

    /**
     * @test
     * @group integration
     */
    public function the_yahoo_finance_api_client_returns_the_correct_data_with_unknown_symbol()
    {
        // Setup
        $yahooFinanceApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        // Do something
        $response = $yahooFinanceApiClient->fetchStockProfile('UNKNOWN_SYMBOL', 'US');

        $stockProfile = json_decode($response->getContent());

        // Make assertions
        $this->assertEquals(400, $response->getStatusCode());
    }    
}