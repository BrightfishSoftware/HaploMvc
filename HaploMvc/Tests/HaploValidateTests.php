<?php
namespace HaploMvc\Tests;

use PHPUnit_Framework_TestCase,
    HaploMvc\Input\HaploValidate;

class HaploValidateTests extends PHPUnit_Framework_TestCase
{
    public function testIsAlpha()
    {
        $this->assertTrue(HaploValidate::isAlpha('012345abcDEF'));
    }

    public function testIsNotAlpha()
    {
        $this->assertFalse(HaploValidate::isAlpha('[],.;][[_-=+-@£$^$&()'));
    }

    public function testIsAlphaWithinLength()
    {
        $this->assertTrue(HaploValidate::isAlpha('01234', 1, 5));
    }

    public function testIsNotAlphaWithinLength()
    {
        $this->assertFalse(HaploValidate::isAlpha('01234', 6, 10));
    }

    public function testIsAlphaAtLeastLength()
    {
        $this->assertTrue(HaploValidate::isAlpha('01234', 1));
    }

    public function testIsAlphaAtMostLength()
    {
        $this->assertTrue(HaploValidate::isAlpha('01234', null, 5));
    }

    public function testIsAlphaNotAtLeastLength()
    {
        $this->assertFalse(HaploValidate::isAlpha('01234', 7));
    }

    public function testIsAlphaNotAtMostLength()
    {
        $this->assertFalse(HaploValidate::isAlpha('01234', null, 4));
    }

    public function testIsYesNo()
    {
        $this->assertTrue(HaploValidate::isYesNo('yes'), 'Checking for "yes"');
        $this->assertTrue(HaploValidate::isYesNo('no'), 'Checking for "no"');
        $this->assertTrue(HaploValidate::isYesNo('Yes'), 'Checking for "Yes"');
        $this->assertTrue(HaploValidate::isYesNo('No'), 'Checking for "No"');
        $this->assertTrue(HaploValidate::isYesNo('YES'), 'Checking for "YES"');
        $this->assertTrue(HaploValidate::isYesNo('NO'), 'Checking for "NO"');
    }

    public function testIsNotYesNo()
    {
        $this->assertFalse(HaploValidate::isYesNo(1), 'Checking for 1');
        $this->assertFalse(HaploValidate::isYesNo(0), 'Checking for 0');
        $this->assertFalse(HaploValidate::isYesNo('test'), 'Checking for "test"');
    }

    public function testIsInRange()
    {
        $this->assertTrue(HaploValidate::inRange(10, 1, 50), 'Checking in range 1-50');
        $this->assertTrue(HaploValidate::inRange(10, 1), 'Checking at least 1');
        $this->assertTrue(HaploValidate::inRange(10, null, 10), 'Checking not more than 10');
    }

    public function testIsNotInRange()
    {
        $this->assertFalse(HaploValidate::inRange(5, 6, 10), 'Checking in range 6-10');
        $this->assertFalse(HaploValidate::inRange(5, 6), 'Checking at least 6');
        $this->assertFalse(HaploValidate::inRange(5, null, 4), 'Checking not more than 4');
    }

    public function testHasLength()
    {
        $this->assertTrue(HaploValidate::hasLength('test', 1, 10), 'Checking between 1-10 characters long');
        $this->assertTrue(HaploValidate::hasLength('test', 1), 'Checking at least 1 character long');
        $this->assertTrue(HaploValidate::hasLength('test', null, 10), 'Checking not greater than 10 characters long');
    }

    public function testDoesNotHaveLength()
    {
        $this->assertFalse(HaploValidate::hasLength('test', 6, 10), 'Checking between 6-10 characters long');
        $this->assertFalse(HaploValidate::hasLength('test', 6), 'Checking at least 6 characters long');
        $this->assertFalse(HaploValidate::hasLength('testtest', null, 4), 'Checking not greater than 4 characters long');
    }

    public function testIsEmail()
    {
        $this->assertTrue(HaploValidate::isEmail('test@test.com'), 'Checking for "test@test.com"');
        $this->assertTrue(HaploValidate::isEmail('test+1@test.com'), 'Checking for "test+1@test.com"');
        $this->assertTrue(HaploValidate::isEmail('test.test@test.com'), 'Checking for "test.test@test.com"');
        $this->assertTrue(HaploValidate::isEmail('test@test.co.uk'), 'Checking for "test@test.co.uk"');
    }

    public function testIsNotEmail()
    {
        $this->assertFalse(HaploValidate::isEmail('test'), 'Checking for "test"');
        $this->assertFalse(HaploValidate::isEmail('test@'), 'Checking for "test@"');
        $this->assertFalse(HaploValidate::isEmail('@'), 'Checking for "@"');
        $this->assertFalse(HaploValidate::isEmail('@test.com'), 'Checking for "@test"');
    }

    public function testIsUrl()
    {
        $this->assertTrue(HaploValidate::isUrl('http://www.test.com/'), 'Checking for "http://www.test.com/"');
        $this->assertTrue(HaploValidate::isUrl('http://test.com/'), 'Checking for "http://test.com/"');
        $this->assertTrue(HaploValidate::isUrl('mailto:test@test.com', array('mailto:')), 'Checking for "mailto:test@test.com"');
        $this->assertTrue(HaploValidate::isUrl('http://test:test@test.com'), 'Checking for "http://test:test@test.com');
        $this->assertTrue(HaploValidate::isUrl('https://www.test.com/'), 'Checking for "https://www.test.com/');
        $this->assertTrue(HaploValidate::isUrl('http://www.test.com:80/'), 'Checking for "http://www.test.com:80/"');
    }

    public function testIsNotUrl()
    {
        $this->assertFalse(HaploValidate::isUrl('test'), 'Checking for "test"');
        $this->assertFalse(HaploValidate::isUrl('file:///test/test.txt'), 'Checking for "file:///test/test.txt"');
        $this->assertFalse(HaploValidate::isUrl('mailto:test@test.com'), 'Checking for "mailto:test@test.com');
    }

    public function testIsHexColor()
    {
        $this->assertTrue(HaploValidate::isHexColor('#ffffff'), 'Checking for "#ffffff"');
        $this->assertTrue(HaploValidate::isHexColor('ffffff'), 'Checking for "ffffff"');
        $this->assertTrue(HaploValidate::isHexColor('#fff'), 'Checking for "#fff"');
        $this->assertTrue(HaploValidate::isHexColor('fff'), 'Checking for "fff"');
        $this->assertTrue(HaploValidate::isHexColor('ffffff', true), 'Checking for "ffffff" with only 6 digit allowed');
        $this->assertTrue(HaploValidate::isHexColor('ffffff', false, false), 'Checking for "ffffff" with # not allowed');
    }

    public function testIsNotHexColor()
    {
        $this->assertFalse(HaploValidate::isHexColor('#dfg'), 'Checking for "#dfg"');
        $this->assertFalse(HaploValidate::isHexColor('#sjross'), 'Checking for "#sjross"');
        $this->assertFalse(HaploValidate::isHexColor('dfg', 'Checking for "dfg"'));
        $this->assertFalse(HaploValidate::isHexColor('sjrosss'), 'Checking for "sjrosss');
        $this->assertFalse(HaploValidate::isHexColor('#fff', true), 'Checking for 6 digit only');
        $this->assertFalse(HaploValidate::isHexColor('#fff', false, false), 'Checking for # not allowed');
    }

    public function testIsEnDayOfWeek()
    {
        $this->assertTrue(HaploValidate::isEnDayOfWeek('Monday'), 'Checking for "Monday"');
        $this->assertTrue(HaploValidate::isEnDayOfWeek('Tuesday'), 'Checking for "Tuesday"');
        $this->assertTrue(HaploValidate::isEnDayOfWeek('monday'), 'Checking for "monday"');
        $this->assertTrue(HaploValidate::isEnDayOfWeek('MONDAY'), 'Checking for "MONDAY"');
    }

    public function testIsNotEnDayOfWeek()
    {
        $this->assertFalse(HaploValidate::isEnDayOfWeek('test'), 'Checking for "test"');
        $this->assertFalse(HaploValidate::isEnDayOfWeek('Mondaytest'), 'Checking for "Mondaytest"');
    }

    public function testIsEnAbbrDayOfWeek()
    {
        $this->assertTrue(HaploValidate::isEnAbbrDayOfWeek('Mo'), 'Checking for "Mo"');
        $this->assertTrue(HaploValidate::isEnAbbrDayOfWeek('Tu'), 'Checking for "Tu"');
        $this->assertTrue(HaploValidate::isEnAbbrDayOfWeek('mo'), 'Checking for "mo"');
        $this->assertTrue(HaploValidate::isEnAbbrDayOfWeek('MO'), 'Checking for "MO"');
    }

    public function testIsNotEnAbbrDayOfWeek()
    {
        $this->assertFalse(HaploValidate::isEnAbbrDayOfWeek('te'), 'Checking for "te"');
        $this->assertFalse(HaploValidate::isEnAbbrDayOfWeek('Motest'), 'Checking for "Motest"');
    }
}
