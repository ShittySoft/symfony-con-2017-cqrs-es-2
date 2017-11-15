<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Building\Domain\DomainEvent\UserCheckedOut;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var null[]
     */
    private $checkedIn = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        $anomaly = \array_key_exists($username, $this->checkedIn);

        $this->recordThat(UserCheckedIn::toBuilding($this->uuid, $username));

        if ($anomaly) {
            $this->recordThat(CheckInAnomalyDetected::inBuilding($this->uuid, $username));
        }
    }

    public function checkOutUser(string $username)
    {
        $anomaly = ! \array_key_exists($username, $this->checkedIn);

        $this->recordThat(UserCheckedOut::ofBuilding($this->uuid, $username));

        if ($anomaly) {
            $this->recordThat(CheckInAnomalyDetected::inBuilding($this->uuid, $username));
        }
    }

    protected function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = $event->uuid();
        $this->name = $event->name();
    }

    protected function whenUserCheckedIn(UserCheckedIn $event)
    {
        $this->checkedIn[$event->username()] = null;
    }

    protected function whenUserCheckedOut(UserCheckedOut $event)
    {
        unset($this->checkedIn[$event->username()]);
    }

    protected function whenCheckInAnomalyDetected(CheckInAnomalyDetected $event)
    {
        // nothing
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
