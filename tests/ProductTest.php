<?php

namespace App\Tests\Product;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testAdd()
    {
        $product = new Product();
        $product->setName("My New Product");
        $result = $product->getName();

        $this->assertEquals("My New Product", $result);
    }
}