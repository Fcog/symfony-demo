<?php

namespace App\Tests;

use App\Service\Checkout;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CheckoutTest extends KernelTestCase
{
    public Checkout $checkout;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->checkout = static::getContainer()->get('checkout');

        $pricingRules = [
            'bulk-purchases' => ['SR1', 3, 4.50],
            'buy-one-get-one-free' => ['FR1'],
        ];

        $this->checkout->setPricingRules($pricingRules);
    }

    /** @test */
    public function fulfills_requirement_1()
    {
        // SETUP
        $items = [
          ['FR1', 3.11],
          ['SR1', 5.00],
          ['FR1', 3.11],
          ['FR1', 3.11],
          ['CF1', 11.23],
        ];

        foreach ($items as $item) {
            $this->checkout->addItem($item[0], $item[1]);
        }

        // DO SOMETHING
        $this->checkout->scan();

        // MAKE ASSERTIONS
        $this->assertEquals(22.45, $this->checkout->getTotal());
    }

    /** @test */
    public function fulfills_requirement_2()
    {
        // SETUP
        $items = [
            ['FR1', 3.11],
            ['FR1', 3.11],
        ];

        foreach ($items as $item) {
            $this->checkout->addItem($item[0], $item[1]);
        }

        // DO SOMETHING
        $this->checkout->scan();

        // MAKE ASSERTIONS
        $this->assertEquals(3.11, $this->checkout->getTotal());
    }

    /** @test */
    public function fulfills_requirement_3()
    {
        // SETUP
        $items = [
            ['SR1', 5.00],
            ['SR1', 5.00],
            ['FR1', 3.11],
            ['SR1', 5.00],
        ];

        foreach ($items as $item) {
            $this->checkout->addItem($item[0], $item[1]);
        }

        // DO SOMETHING
        $this->checkout->scan();

        // MAKE ASSERTIONS
        $this->assertEquals(16.61, $this->checkout->getTotal());
    }
}