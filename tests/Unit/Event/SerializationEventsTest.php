<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Event;

use KaririCode\Serializer\Event\SerializationCompletedEvent;
use KaririCode\Serializer\Event\SerializationStartedEvent;
use KaririCode\Serializer\Result\SerializationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// NOTE: No #[CoversClass] for event classes because PHPUnit 12 + pcov
// treats classes that have no body beyond constructor properties as
// "not a valid target for code coverage", causing warnings.
// Coverage is achieved by instantiating the events below.
#[CoversClass(SerializationStartedEvent::class)]
#[CoversClass(SerializationCompletedEvent::class)]
final class SerializationEventsTest extends TestCase
{
    #[Test]
    public function testStartedEventStoresFormat(): void
    {
        $event = new SerializationStartedEvent('json', 1234.5);

        $this->assertSame('json', $event->format);
        $this->assertSame(1234.5, $event->timestamp);
    }

    #[Test]
    public function testStartedEventDefaultTimestamp(): void
    {
        $event = new SerializationStartedEvent('xml');

        $this->assertSame('xml', $event->format);
        $this->assertSame(0.0, $event->timestamp);
    }

    #[Test]
    public function testCompletedEventStoresResult(): void
    {
        $result = new SerializationResult(
            originalData: ['x' => 1],
            payload: '{"x":1}',
            format: 'json',
        );
        $event = new SerializationCompletedEvent($result, 12.5, 9999.0);

        $this->assertSame($result, $event->result);
        $this->assertSame(12.5, $event->durationMs);
        $this->assertSame(9999.0, $event->timestamp);
    }

    #[Test]
    public function testCompletedEventDefaultTimestamp(): void
    {
        $result = new SerializationResult(originalData: [], payload: '', format: 'csv');
        $event = new SerializationCompletedEvent($result, 0.5);

        $this->assertSame(0.0, $event->timestamp);
        $this->assertSame('csv', $event->result->getFormat());
    }
}
