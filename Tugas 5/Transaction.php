<?php
declare(strict_types=1);
 
/**
 * Merepresentasikan satu transaksi keuangan (deposit atau penarikan).
 */
class Transaction
{
    public function __construct(
        private readonly string $id,
        private readonly string $type,
        private readonly float $amount
    ) {
    }
 
    public function getId(): string
    {
        return $this->id;
    }
 
    public function getType(): string
    {
        return $this->type;
    }
 
    public function getAmount(): float
    {
        return $this->amount;
    }