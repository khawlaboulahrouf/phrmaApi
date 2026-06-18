<?php
// src/Entity/Product.php

namespace PharmaFEFO\Entity;

class Product implements \JsonSerializable
{
    private int $id;
    private string $name;
    private string $reference;
    private float $unitPrice;

    public function __construct(int $id, string $name, string $reference, float $unitPrice = 0.0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->reference = $reference;
        $this->unitPrice = $unitPrice;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * Sérialisation JSON pour les réponses API (US Part 2 - JsonSerializable).
     */
    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'reference'  => $this->reference,
            'unit_price' => $this->unitPrice,
        ];
    }
}
