<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * Koongo - Rewrite of Magento\InventoryReservations\Model\Reservation and Magento\InventoryReservationsApi\Model\ReservationInterface
 * For transfer of reservation via API
 */

namespace Nostress\Koongo\Api;

/**
 * The entity responsible for reservations, created to keep inventory amount (product quantity) up-to-date.
 * It is created to have a state between order creation and inventory deduction (deduction of specific SourceItems).
 *
 * Reservations are designed to be immutable entities.
 *
 * @api
 */
interface ReservationKoongoInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCK_ID = 'stock_id';
    const SKU = 'sku';
    const QUANTITY = 'quantity';
    const METADATA = 'metadata';

    /**
     * Get Stock Id
     *
     * @return int
     */
    public function getStockId(): int;

    /**
     * Get Product SKU
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Get Product Qty
     *
     * This value can be positive (>0) or negative (<0) depending on the Reservation semantic.
     *
     * For example, when an Order is placed, a Reservation with negative quantity is appended.
     * When that Order is processed and the SourceItems related to ordered products are updated, a Reservation with
     * positive quantity is appended to neglect the first one.
     *
     * @return float
     */
    public function getQuantity(): float;

    /**
     * Get Reservation Metadata
     *
     * Metadata is used to store serialized data that encapsulates the semantic of a Reservation.
     *
     * @return string
     */
    public function getMetadata(): ?string;

    /**
     * @inheritdoc
     */
    public function setStockId(int $stockId);

    /**
     * @inheritdoc
     */
    public function setSku($sku);

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity);

    /**
     * @inheritdoc
     */
    public function setMetadata($metadata);
}
