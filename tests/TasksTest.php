<?php
use PHPUnit\Framework\TestCase;
use App\Tasks;

class TasksTest extends TestCase
{
    public function testHealthIsOk(): void
    {
        $this->assertSame('ok', Tasks::health()['status']);
    }

    public function testEmptyTitleIsInvalid(): void
    {
        $this->assertFalse(Tasks::isValidTitle('   '));
    }

    public function testNormalTitleIsValid(): void
    {
        $this->assertTrue(Tasks::isValidTitle('Buy milk'));
    }

    public function testRouteHomeAndHealth(): void
    {
        $this->assertSame(['action' => 'home'], Tasks::route('GET', '/'));
        $this->assertSame(['action' => 'health'], Tasks::route('GET', '/health'));
    }

    public function testRouteListAndCreate(): void
    {
        $this->assertSame(['action' => 'list'], Tasks::route('GET', '/tasks'));
        $this->assertSame(['action' => 'create'], Tasks::route('POST', '/tasks'));
    }

    public function testRouteToggle(): void
    {
        $this->assertSame(['action' => 'toggle', 'id' => 5], Tasks::route('POST', '/tasks/5/toggle'));
    }

    public function testRouteDelete(): void
    {
        $this->assertSame(['action' => 'delete', 'id' => 7], Tasks::route('DELETE', '/tasks/7'));
    }

    public function testUnknownRouteIsNotFound(): void
    {
        $this->assertSame(['action' => 'not_found'], Tasks::route('GET', '/nimporte'));
        $this->assertSame(['action' => 'not_found'], Tasks::route('GET', '/tasks/5/toggle'));
    }
}