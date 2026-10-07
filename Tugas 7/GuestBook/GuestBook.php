<?php
declare(strict_types=1);

class GuestBook
{
    public function __construct(private readonly PDO $pdo)
    {
    }
}
