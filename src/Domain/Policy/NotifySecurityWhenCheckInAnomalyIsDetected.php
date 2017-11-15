<?php

declare(strict_types=1);

namespace Building\Domain\Policy;

use Building\Domain\Command\NotifySecurityOfCheckInAnomaly;
use Building\Domain\DomainEvent\CheckInAnomalyDetected;

final class NotifySecurityWhenCheckInAnomalyIsDetected
{
    /**
     * @var callable
     */
    private $runCommand;

    public function __construct(callable $runCommand)
    {
        $this->runCommand = $runCommand;
    }

    public function __invoke(CheckInAnomalyDetected $event) : void
    {
        ($this->runCommand)(NotifySecurityOfCheckInAnomaly::inBuilding(
            $event->buildingId(),
            $event->username()
        ));
    }
}
