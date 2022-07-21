<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * Koongo - Rewrite of Magento\InventoryReservations\Model\Reservation and Magento\InventoryReservationsApi\Model\ReservationInterface
 * For transfer of reservation via API
 */

namespace Nostress\Koongo\Model;

use Nostress\Koongo\Api\ReservationKoongoInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class ReservationKoongo implements ReservationKoongoInterface
{
    /**
     * @var int
     */
    private $stockId;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $metadata;

    /**
     * @inheritdoc
     */
    public function getStockId(): int
    {
        return $this->stockId;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(): ?string
    {
        return $this->metadata;
    }

    /**
     * @inheritdoc
     */
    public function setStockId(int $stockId)
    {
        $this->stockId = $stockId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }
}
