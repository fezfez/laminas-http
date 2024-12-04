<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use DateTime;
use DateTimeZone;
use Laminas\Http\Header\Date;
use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function time;

use const PHP_VERSION_ID;

class DateTest extends TestCase
{
    public function tearDown(): void
    {
        // set to RFC default date format
        Date::setDateFormat(Date::DATE_RFC1123);
    }

    public function testDateFromStringCreatesValidDateHeader(): void
    {
        $dateHeader = Date::fromString('Date: Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertInstanceOf(HeaderInterface::class, $dateHeader);
        $this->assertInstanceOf(Date::class, $dateHeader);
    }

    public function testDateFromTimeStringCreatesValidDateHeader(): void
    {
        $dateHeader = Date::fromTimeString('+12 hours');

        $this->assertInstanceOf(HeaderInterface::class, $dateHeader);
        $this->assertInstanceOf(Date::class, $dateHeader);

        $date     = new DateTime('now', new DateTimeZone('GMT'));
        $interval = $dateHeader->date()->diff($date, true);

        if (PHP_VERSION_ID >= 70200) {
            $this->assertSame('+11 hours 59 minutes 59 seconds', $interval->format('%R%H hours %I minutes %S seconds'));
            $this->assertLessThan(1, $interval->f);
            $this->assertGreaterThan(0, $interval->f);
        } else {
            $this->assertSame('+12 hours 00 minutes 00 seconds', $interval->format('%R%H hours %I minutes %S seconds'));
        }
    }

    public function testDateFromTimestampCreatesValidDateHeader(): void
    {
        $dateHeader = Date::fromTimestamp(time() + 12 * 60 * 60);

        $this->assertInstanceOf(HeaderInterface::class, $dateHeader);
        $this->assertInstanceOf(Date::class, $dateHeader);

        $date     = new DateTime('now', new DateTimeZone('GMT'));
        $interval = $dateHeader->date()->diff($date, true);

        if (PHP_VERSION_ID >= 70200) {
            $this->assertSame('+11 hours 59 minutes 59 seconds', $interval->format('%R%H hours %I minutes %S seconds'));
            $this->assertLessThan(1, $interval->f);
            $this->assertGreaterThan(0, $interval->f);
        } else {
            $this->assertSame('+12 hours 00 minutes 00 seconds', $interval->format('%R%H hours %I minutes %S seconds'));
        }
    }

    public function testDateFromTimeStringDetectsBadInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Date::fromTimeString('3 Days of the Condor');
    }

    public function testDateFromTimestampDetectsBadInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Date::fromTimestamp('The Day of the Jackal');
    }

    public function testDateGetFieldNameReturnsHeaderName(): void
    {
        $dateHeader = new Date();
        $this->assertEquals('Date', $dateHeader->getFieldName());
    }

    public function testDateGetFieldValueReturnsProperValue(): void
    {
        $dateHeader = new Date();
        $dateHeader->setDate('Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertEquals('Sun, 06 Nov 1994 08:49:37 GMT', $dateHeader->getFieldValue());
    }

    public function testDateToStringReturnsHeaderFormattedString(): void
    {
        $dateHeader = new Date();
        $dateHeader->setDate('Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertEquals('Date: Sun, 06 Nov 1994 08:49:37 GMT', $dateHeader->toString());
    }

    // Implementation specific tests here

    // phpcs:ignore Squiz.Commenting.FunctionComment.WrongStyle
    public function testDateReturnsDateTimeObject(): void
    {
        $dateHeader = new Date();
        $this->assertInstanceOf(DateTime::class, $dateHeader->date());
    }

    public function testDateFromStringCreatesValidDateTime(): void
    {
        $dateHeader = Date::fromString('Date: Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertInstanceOf(DateTime::class, $dateHeader->date());
        $this->assertEquals('Sun, 06 Nov 1994 08:49:37 GMT', $dateHeader->date()->format('D, d M Y H:i:s \G\M\T'));
    }

    public function testDateReturnsProperlyFormattedDate(): void
    {
        $date = new DateTime('now', new DateTimeZone('GMT'));

        $dateHeader = new Date();
        $dateHeader->setDate($date);
        $this->assertEquals($date->format('D, d M Y H:i:s \G\M\T'), $dateHeader->getDate());
    }

    public function testDateThrowsExceptionForInvalidDate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date');
        $dateHeader = new Date();
        $dateHeader->setDate('~~~~');
    }

    public function testDateCanCompareDates(): void
    {
        $dateHeader = new Date();
        $dateHeader->setDate('1 day ago');
        $this->assertEquals(-1, $dateHeader->compareTo(new DateTime('now')));
    }

    public function testDateCanOutputDatesInOldFormats(): void
    {
        Date::setDateFormat(Date::DATE_ANSIC);

        $dateHeader = new Date();
        $dateHeader->setDate('Sun, 06 Nov 1994 08:49:37 GMT');

        $this->assertEquals('Date: Sun Nov 6 08:49:37 1994', $dateHeader->toString());
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Date::fromString("Date: Sun, 06 Nov 1994 08:49:37 GMT\r\n\r\nevilContent");
    }
}
