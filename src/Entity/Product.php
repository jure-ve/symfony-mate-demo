<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 150)]
        private string $name,
        #[ORM\Column]
        private float $price,
        #[ORM\ManyToOne(inversedBy: 'products')]
        #[ORM\JoinColumn(nullable: false)]
        private Category $category,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
