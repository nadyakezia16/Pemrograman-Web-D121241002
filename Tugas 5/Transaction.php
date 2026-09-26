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
    /**
     * Memproses transaksi terhadap saldo saat ini.
     * Mengembalikan saldo baru jika berhasil.
     *
     * @throws InvalidArgumentException jika jenis transaksi tidak dikenali
     * @throws RuntimeException jika saldo tidak mencukupi untuk penarikan
     */
    public function process(float $currentBalance): float
    {
        return match ($this->type) {
            'deposit'    => $currentBalance + $this->amount,
            'withdrawal' => $this->processWithdrawal($currentBalance),
            default      => throw new InvalidArgumentException('Jenis transaksi tidak dikenali.'),
        };
    }
 
    private function processWithdrawal(float $currentBalance): float
    {
        if ($this->amount > $currentBalance) {
            throw new RuntimeException('Saldo tidak mencukupi untuk melakukan penarikan.');
        }
 
        return $currentBalance - $this->amount;
    }
        /**
     * Representasi array untuk disimpan ke dalam session.
     */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'type'   => $this->type,
            'amount' => $this->amount,
        ];
    }
}