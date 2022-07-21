<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce).
 * With the purchase, download of the software or the installation of the software
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
 * Model for Koongo API webhooks repository
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model;

use Magento\Framework\ObjectManagerInterface;
use Nostress\Koongo\Api\ReservationKoongoInterface;
use Nostress\Koongo\Api\ReservationRepositoryInterface;

class ReservationRepository implements ReservationRepositoryInterface
{
    /**
     * @var Magento\InventoryReservationsApi\Model\ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var Magento\InventoryReservationsApi\Model\AppendReservationsInterface
     */
    private $appendReservations;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        if (class_exists("Magento\InventoryReservationsApi\Model\ReservationBuilderInterface")) {
            $this->appendReservations = $objectManager->get("Magento\InventoryReservationsApi\Model\AppendReservationsInterface");
            $this->reservationBuilder = $objectManager->get("Magento\InventoryReservationsApi\Model\ReservationBuilderInterface");
        }
    }

    /**
     * Create reservation
     *
     * @param \Nostress\Koongo\Api\ReservationKoongoInterface $reservation
     * @return \Magento\InventoryReservationsApi\Model\ReservationInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(ReservationKoongoInterface $reservation)
    {
        if ($this->reservationBuilder === null) {
            return null;
        }

        $reservation = $this->reservationBuilder
                    ->setSku($reservation->getSku())
                    ->setQuantity((float)$reservation->getQuantity())
                    ->setStockId($reservation->getStockId())
                    ->setMetadata($reservation->getMetadata())
                    ->build();
        $this->appendReservations->execute([$reservation]);
        return $reservation;
    }
}
