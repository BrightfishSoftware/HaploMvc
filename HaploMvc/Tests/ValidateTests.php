<?php
namespace HaploMvc\Tests;

use PHPUnit_Framework_TestCase;
use HaploMvc\Input\Validate;

class ValidateTests extends PHPUnit_Framework_TestCase
{
    public function testIsAlpha()
    {
        $this->assertTrue(Validate::isAlpha('012345abcDEF'));
    }

    public function testIsNotAlpha()
    {
        $this->assertFalse(Validate::isAlpha('[],.;][[_-=+-@£$^$&()'));
    }

    public function testIsAlphaWithinLength()
    {
        $this->assertTrue(Validate::isAlpha('01234', 1, 5));
    }

    public function testIsNotAlphaWithinLength()
    {
        $this->assertFalse(Validate::isAlpha('01234', 6, 10));
    }

    public function testIsAlphaAtLeastLength()
    {
        $this->assertTrue(Validate::isAlpha('01234', 1));
    }

    public function testIsAlphaAtMostLength()
    {
        $this->assertTrue(Validate::isAlpha('01234', null, 5));
    }

    public function testIsAlphaNotAtLeastLength()
    {
        $this->assertFalse(Validate::isAlpha('01234', 7));
    }

    public function testIsAlphaNotAtMostLength()
    {
        $this->assertFalse(Validate::isAlpha('01234', null, 4));
    }

    public function testIsYesNo()
    {
        $this->assertTrue(Validate::isYesNo('yes'), 'Checking for "yes"');
        $this->assertTrue(Validate::isYesNo('no'), 'Checking for "no"');
        $this->assertTrue(Validate::isYesNo('Yes'), 'Checking for "Yes"');
        $this->assertTrue(Validate::isYesNo('No'), 'Checking for "No"');
        $this->assertTrue(Validate::isYesNo('YES'), 'Checking for "YES"');
        $this->assertTrue(Validate::isYesNo('NO'), 'Checking for "NO"');
    }

    public function testIsNotYesNo()
    {
        $this->assertFalse(Validate::isYesNo(1), 'Checking for 1');
        $this->assertFalse(Validate::isYesNo(0), 'Checking for 0');
        $this->assertFalse(Validate::isYesNo('test'), 'Checking for "test"');
    }

    public function testIsInRange()
    {
        $this->assertTrue(Validate::inRange(10, 1, 50), 'Checking in range 1-50');
        $this->assertTrue(Validate::inRange(10, 1), 'Checking at least 1');
        $this->assertTrue(Validate::inRange(10, null, 10), 'Checking not more than 10');
    }

    public function testIsNotInRange()
    {
        $this->assertFalse(Validate::inRange(5, 6, 10), 'Checking in range 6-10');
        $this->assertFalse(Validate::inRange(5, 6), 'Checking at least 6');
        $this->assertFalse(Validate::inRange(5, null, 4), 'Checking not more than 4');
    }

    public function testHasLength()
    {
        $this->assertTrue(Validate::hasLength('test', 1, 10), 'Checking between 1-10 characters long');
        $this->assertTrue(Validate::hasLength('test', 1), 'Checking at least 1 character long');
        $this->assertTrue(Validate::hasLength('test', null, 10), 'Checking not greater than 10 characters long');
    }

    public function testDoesNotHaveLength()
    {
        $this->assertFalse(Validate::hasLength('test', 6, 10), 'Checking between 6-10 characters long');
        $this->assertFalse(Validate::hasLength('test', 6), 'Checking at least 6 characters long');
        $this->assertFalse(Validate::hasLength('testtest', null, 4), 'Checking not greater than 4 characters long');
    }

    public function testIsEmail()
    {
        $this->assertTrue(Validate::isEmail('test@test.com'), 'Checking for "test@test.com"');
        $this->assertTrue(Validate::isEmail('test+1@test.com'), 'Checking for "test+1@test.com"');
        $this->assertTrue(Validate::isEmail('test.test@test.com'), 'Checking for "test.test@test.com"');
        $this->assertTrue(Validate::isEmail('test@test.co.uk'), 'Checking for "test@test.co.uk"');
    }

    public function testIsNotEmail()
    {
        $this->assertFalse(Validate::isEmail('test'), 'Checking for "test"');
        $this->assertFalse(Validate::isEmail('test@'), 'Checking for "test@"');
        $this->assertFalse(Validate::isEmail('@'), 'Checking for "@"');
        $this->assertFalse(Validate::isEmail('@test.com'), 'Checking for "@test"');
    }

    public function testIsUrl()
    {
        $this->assertTrue(Validate::isUrl('http://www.test.com/'), 'Checking for "http://www.test.com/"');
        $this->assertTrue(Validate::isUrl('http://test.com/'), 'Checking for "http://test.com/"');
        $this->assertTrue(Validate::isUrl('mailto:test@test.com', ['mailto:']), 'Checking for "mailto:test@test.com"');
        $this->assertTrue(Validate::isUrl('http://test:test@test.com'), 'Checking for "http://test:test@test.com');
        $this->assertTrue(Validate::isUrl('https://www.test.com/'), 'Checking for "https://www.test.com/');
        $this->assertTrue(Validate::isUrl('http://www.test.com:80/'), 'Checking for "http://www.test.com:80/"');
    }

    public function testIsNotUrl()
    {
        $this->assertFalse(Validate::isUrl('test'), 'Checking for "test"');
        $this->assertFalse(Validate::isUrl('file:///test/test.txt'), 'Checking for "file:///test/test.txt"');
        $this->assertFalse(Validate::isUrl('mailto:test@test.com'), 'Checking for "mailto:test@test.com');
    }

    public function testIsHexColor()
    {
        $this->assertTrue(Validate::isHexColor('#ffffff'), 'Checking for "#ffffff"');
        $this->assertTrue(Validate::isHexColor('ffffff'), 'Checking for "ffffff"');
        $this->assertTrue(Validate::isHexColor('#fff'), 'Checking for "#fff"');
        $this->assertTrue(Validate::isHexColor('fff'), 'Checking for "fff"');
        $this->assertTrue(Validate::isHexColor('ffffff', true), 'Checking for "ffffff" with only 6 digit allowed');
        $this->assertTrue(Validate::isHexColor('ffffff', false, false), 'Checking for "ffffff" with # not allowed');
    }

    public function testIsNotHexColor()
    {
        $this->assertFalse(Validate::isHexColor('#dfg'), 'Checking for "#dfg"');
        $this->assertFalse(Validate::isHexColor('#sjross'), 'Checking for "#sjross"');
        $this->assertFalse(Validate::isHexColor('dfg', 'Checking for "dfg"'));
        $this->assertFalse(Validate::isHexColor('sjrosss'), 'Checking for "sjrosss');
        $this->assertFalse(Validate::isHexColor('#fff', true), 'Checking for 6 digit only');
        $this->assertFalse(Validate::isHexColor('#fff', false, false), 'Checking for # not allowed');
    }

    public function testIsEnDayOfWeek()
    {
        $this->assertTrue(Validate::isEnDayOfWeek('Monday'), 'Checking for "Monday"');
        $this->assertTrue(Validate::isEnDayOfWeek('Tuesday'), 'Checking for "Tuesday"');
        $this->assertTrue(Validate::isEnDayOfWeek('monday'), 'Checking for "monday"');
        $this->assertTrue(Validate::isEnDayOfWeek('MONDAY'), 'Checking for "MONDAY"');
    }

    public function testIsNotEnDayOfWeek()
    {
        $this->assertFalse(Validate::isEnDayOfWeek('test'), 'Checking for "test"');
        $this->assertFalse(Validate::isEnDayOfWeek('Mondaytest'), 'Checking for "Mondaytest"');
    }

    public function testIsEnAbbrDayOfWeek()
    {
        $this->assertTrue(Validate::isEnAbbrDayOfWeek('Mo'), 'Checking for "Mo"');
        $this->assertTrue(Validate::isEnAbbrDayOfWeek('Tu'), 'Checking for "Tu"');
        $this->assertTrue(Validate::isEnAbbrDayOfWeek('mo'), 'Checking for "mo"');
        $this->assertTrue(Validate::isEnAbbrDayOfWeek('MO'), 'Checking for "MO"');
    }

    public function testIsNotEnAbbrDayOfWeek()
    {
        $this->assertFalse(Validate::isEnAbbrDayOfWeek('te'), 'Checking for "te"');
        $this->assertFalse(Validate::isEnAbbrDayOfWeek('Motest'), 'Checking for "Motest"');
    }
}
