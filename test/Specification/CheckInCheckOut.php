<?php

namespace Specification;

use Assert\Assertion;
use Behat\Behat\Context\Context;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /**
     * @var AggregateChanged[]
     */
    private $pastEvents = [];

    /**
     * @var Building|null
     */
    private $building;

    /**
     * @var AggregateChanged[]|null
     */
    private $recordedEvents;

    /**
     * @Given a building was registered
     */
    public function a_building_was_registered() : void
    {
        $this->addPastEvent(NewBuildingWasRegistered::occur((string) Uuid::uuid4(), ['name' => 'A place']));
    }

    /**
     * @When the user checks into the building
     */
    public function the_user_checks_into_the_building() : void
    {
        $this->building()->checkInUser('Some random guy');
    }

    /**
     * @Then the user should have been checked into the building
     *
     * @throws \Assert\AssertionFailedException
     */
    public function the_user_should_have_been_checked_into_the_building()
    {
        Assertion::isInstanceOf($this->nextRecordedEvent(), UserCheckedIn::class);
    }

    private function addPastEvent(AggregateChanged $event) : void
    {
        $this->pastEvents[] = $event;
    }

    private function building() : Building
    {
        if ($this->building) {
            return $this->building;
        }

        return $this->building = (new AggregateTranslator())->reconstituteAggregateFromHistory(
            AggregateType::fromAggregateRootClass(Building::class),
            new \ArrayIterator($this->pastEvents)
        );
    }

    private function nextRecordedEvent() : AggregateChanged
    {
        if (null !== $this->recordedEvents) {
            return \array_shift($this->recordedEvents);
        }

        $this->recordedEvents = (new AggregateTranslator())->extractPendingStreamEvents($this->building());

        return \array_shift($this->recordedEvents);
    }
}
