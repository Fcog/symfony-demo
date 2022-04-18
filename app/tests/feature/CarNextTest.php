<?php

namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CarNextTest extends KernelTestCase
{
    protected function setUp(): void
    {

    }


    public function solution($n) {
        if ($n === 1 or $n === 0) {
            return 1;
        }

        return $this->solution($n - 1) + $this->solution($n - 2);
    }

    /** @test */
    public function fulfills_requirement_1()
    {
        // SETUP
        $n = 1;

        // DO SOMETHING


        // MAKE ASSERTIONS
        $this->assertEquals(1, $this->solution($n));
    }

    /** @test */
    public function fulfills_requirement_2()
    {
        // SETUP
        $n = 3;

        // DO SOMETHING


        // MAKE ASSERTIONS
        $this->assertEquals(3, $this->solution($n));
    }

    /** @test */
    public function fulfills_requirement_3()
    {
        // SETUP
        $n = 4;

        // DO SOMETHING


        // MAKE ASSERTIONS
        $this->assertEquals(5, $this->solution($n));
    }

    /** @test */
    public function fulfills_requirement_4()
    {
        // SETUP
        $n = 5;

        // DO SOMETHING


        // MAKE ASSERTIONS
        $this->assertEquals(8, $this->solution($n));
    }
}