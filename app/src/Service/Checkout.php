<?php

namespace App\Service;

class Checkout
{
    private array $items;
    private array $pricingRules;

    public function setPricingRules(array $pricingRules): void
    {
        $this->pricingRules = $pricingRules;
    }

    public function addItem(string $code, float $price): void
    {
        $this->items[] = [
            $code,
            $price,
        ];
    }

    public function scan(): void
    {
        foreach ($this->pricingRules as $id => $pricingRule) {
            if ($id === 'buy-one-get-one-free') {
                $this->calculateBuyOneGetOneFreePromo($pricingRule);
            }
            if ($id === 'bulk-purchases') {
                $this->calculateBulkPurchasesPromo($pricingRule);
            }
        }
    }

    public function getTotal(): float
    {
        return array_reduce(
            $this->items,
            function ($sum, $item) {
                return $sum + $item[1];
            },
            0
        );
    }

    private function calculateBuyOneGetOneFreePromo(array $rule)
    {
        $productCount = 0;

        foreach ($this->items as $index => $item) {
            if ($item[0] === $rule[0]) {
                ++$productCount;

                if ($productCount === 2) {
                    $this->items[$index][1] = 0;

                    $productCount = 0;
                }
            }
        }
    }

    private function calculateBulkPurchasesPromo(array $rule)
    {
        $productCount = 0;

        foreach ($this->items as $item) {
            if ($item[0] === $rule[0]) {
                ++$productCount;
            }
        }

        if ($productCount >= $rule[1]) {
            foreach ($this->items as $index => $item) {
                if ($item[0] === $rule[0]) {
                    $this->items[$index][1] = $rule[2];
                }
            }
        }
    }
}