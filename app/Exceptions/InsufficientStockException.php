<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly int $requestedQuantity,
        public readonly int $availableStock,
    ) {
        parent::__construct("Stok {$productName} tidak mencukupi.");
    }

    /**
     * @return array{product_id: int, product_name: string, requested_quantity: int, available_stock: int}
     */
    public function context(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'requested_quantity' => $this->requestedQuantity,
            'available_stock' => $this->availableStock,
        ];
    }
}
