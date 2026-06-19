<?php


namespace PharmaFEFO\Entity;

use DateTime;
use PharmaFEFO\Enum\BatchStatus;

class StockBatch implements \JsonSerializable
{
    private int $id;
    private int $productId;
    private string $lotNumber;
    private int $quantity;
    private DateTime $expiryDate;
    private BatchStatus $status;


    private ?string $productName = null;
    private ?string $productReference = null;

  
    private int $warningDays = 90;
    private int $criticalDays = 30;

    public function __construct(
        int $id,
        int $productId,
        string $lotNumber,
        int $quantity,
        DateTime $expiryDate,
        BatchStatus $status = BatchStatus::OK
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->lotNumber = $lotNumber;
        $this->quantity = $quantity;
        $this->expiryDate = $expiryDate;
        $this->status = $status;
    }



    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getLotNumber(): string
    {
        return $this->lotNumber;
    }

    public function setLotNumber(string $lotNumber): void
    {
        $this->lotNumber = $lotNumber;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getExpiryDate(): DateTime
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(DateTime $expiryDate): void
    {
        $this->expiryDate = $expiryDate;
    }

    public function getStatus(): BatchStatus
    {
        return $this->status;
    }

    public function setStatus(BatchStatus $status): void
    {
        $this->status = $status;
    }

 
    public function getDaysToExpiry(): int
    {
        $now = new DateTime('today');
        $diff = $now->diff($this->expiryDate);
        $days = (int) $diff->format('%a');

        return $this->expiryDate < $now ? -$days : $days;
    }

    public function isExpired(): bool
    {
        return $this->getDaysToExpiry() < 0;
    }

   
    public function getCriticality(int $warningDays = 90, int $criticalDays = 30): string
    {
        if ($this->isExpired()) {
            return 'EXPIRED';
        }

        $days = $this->getDaysToExpiry();

        if ($days < $criticalDays) {
            return 'CRITICAL'; 
        }

        if ($days < $warningDays) {
            return 'WARNING'; 
        }

        return 'OK';
    }

    /**
     * Décrémente le stock du lot (sortie FEFO).
     *
     * @throws \InvalidArgumentException si la quantité demandée dépasse le stock disponible
     */
    public function decreaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La quantité doit être positive.');
        }

        if ($quantity > $this->quantity) {
            throw new \InvalidArgumentException(
                "Stock insuffisant sur le lot {$this->lotNumber} (disponible: {$this->quantity}, demandé: {$quantity})."
            );
        }

        $this->quantity -= $quantity;
    }

 
    public function setProductInfo(string $name, string $reference): void
    {
        $this->productName = $name;
        $this->productReference = $reference;
    }

    public function setThresholds(int $warningDays, int $criticalDays): void
    {
        $this->warningDays = $warningDays;
        $this->criticalDays = $criticalDays;
    }

   
    public function jsonSerialize(): array
    {
        return [
            'id'              => $this->id,
            'product_id'      => $this->productId,
            'product_name'    => $this->productName,
            'product_reference' => $this->productReference,
            'lot_number'      => $this->lotNumber,
            'quantity'        => $this->quantity,
            'expiry_date'     => $this->expiryDate->format('Y-m-d'),
            'days_to_expiry'  => $this->getDaysToExpiry(),
            'status'          => $this->status->value,
            'criticality'     => $this->getCriticality($this->warningDays, $this->criticalDays),
        ];
    }

   
    public function markAsExpired(): void
    {
        $this->status = BatchStatus::EXPIRED;
        $this->quantity = 0;
    }
}
