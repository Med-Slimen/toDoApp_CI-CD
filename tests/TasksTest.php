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
        $this->assertFalse(Tasks::isValidTitle('Buy milk'));
    }
}
