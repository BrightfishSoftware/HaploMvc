<?php
namespace HaploMvc\Tests;

use PHPUnit_Framework_TestCase,
    HaploMvc\HaploValidate;

class HaploValidateTests extends PHPUnit_Framework_TestCase {
    public function test_is_alpha() {
        $this->assertTrue(HaploValidate::is_alpha('012345abcDEF'));
    }

    public function test_is_not_alpha() {
        $this->assertFalse(HaploValidate::is_alpha('[],.;][[_-=+-@£$^$&()'));
    }

    public function test_is_alpha_within_length() {
        $this->assertTrue(HaploValidate::is_alpha('01234', 1, 5));
    }

    public function test_is_not_alpha_within_length() {
        $this->assertFalse(HaploValidate::is_alpha('01234', 6, 10));
    }

    public function test_is_alpha_at_least_length() {
        $this->assertTrue(HaploValidate::is_alpha('01234', 1));
    }

    public function test_is_alpha_at_most_length() {
        $this->assertTrue(HaploValidate::is_alpha('01234', null, 5));
    }

    public function test_is_alpha_not_at_least_length() {
        $this->assertFalse(HaploValidate::is_alpha('01234', 7));
    }

    public function test_is_alpha_not_at_most_length() {
        $this->assertFalse(HaploValidate::is_alpha('01234', null, 4));
    }

    public function test_is_yes_no() {
        $this->assertTrue(HaploValidate::is_yes_no('yes'), 'Checking for "yes"');
        $this->assertTrue(HaploValidate::is_yes_no('no'), 'Checking for "no"');
        $this->assertTrue(HaploValidate::is_yes_no('Yes'), 'Checking for "Yes"');
        $this->assertTrue(HaploValidate::is_yes_no('No'), 'Checking for "No"');
        $this->assertTrue(HaploValidate::is_yes_no('YES'), 'Checking for "YES"');
        $this->assertTrue(HaploValidate::is_yes_no('NO'), 'Checking for "NO"');
    }

    public function test_is_not_yes_no() {
        $this->assertFalse(HaploValidate::is_yes_no(1), 'Checking for 1');
        $this->assertFalse(HaploValidate::is_yes_no(0), 'Checking for 0');
        $this->assertFalse(HaploValidate::is_yes_no('test'), 'Checking for "test"');
    }

    public function test_is_in_range() {
        $this->assertTrue(HaploValidate::in_range(10, 1, 50), 'Checking in range 1-50');
        $this->assertTrue(HaploValidate::in_range(10, 1), 'Checking at least 1');
        $this->assertTrue(HaploValidate::in_range(10, null, 10), 'Checking not more than 10');
    }

    public function test_is_not_in_range() {
        $this->assertFalse(HaploValidate::in_range(5, 6, 10), 'Checking in range 6-10');
        $this->assertFalse(HaploValidate::in_range(5, 6), 'Checking at least 6');
        $this->assertFalse(HaploValidate::in_range(5, null, 4), 'Checking not more than 4');
    }

    public function test_has_length() {
        $this->assertTrue(HaploValidate::has_length('test', 1, 10), 'Checking between 1-10 characters long');
        $this->assertTrue(HaploValidate::has_length('test', 1), 'Checking at least 1 character long');
        $this->assertTrue(HaploValidate::has_length('test', null, 10), 'Checking not greater than 10 characters long');
    }

    public function test_does_not_have_length() {
        $this->assertFalse(HaploValidate::has_length('test', 6, 10), 'Checking between 6-10 characters long');
        $this->assertFalse(HaploValidate::has_length('test', 6), 'Checking at least 6 characters long');
        $this->assertFalse(HaploValidate::has_length('testtest', null, 4), 'Checking not greater than 4 characters long');
    }

    public function test_is_email() {
        $this->assertTrue(HaploValidate::is_email('test@test.com'), 'Checking for "test@test.com"');
        $this->assertTrue(HaploValidate::is_email('test+1@test.com'), 'Checking for "test+1@test.com"');
        $this->assertTrue(HaploValidate::is_email('test.test@test.com'), 'Checking for "test.test@test.com"');
        $this->assertTrue(HaploValidate::is_email('test@test.co.uk'), 'Checking for "test@test.co.uk"');
    }

    public function test_is_not_email() {
        $this->assertFalse(HaploValidate::is_email('test'), 'Checking for "test"');
        $this->assertFalse(HaploValidate::is_email('test@'), 'Checking for "test@"');
        $this->assertFalse(HaploValidate::is_email('@'), 'Checking for "@"');
        $this->assertFalse(HaploValidate::is_email('@test.com'), 'Checking for "@test"');
    }

    public function test_is_url() {
        $this->assertTrue(HaploValidate::is_url('http://www.test.com/'), 'Checking for "http://www.test.com/"');
        $this->assertTrue(HaploValidate::is_url('http://test.com/'), 'Checking for "http://test.com/"');
        $this->assertTrue(HaploValidate::is_url('mailto:test@test.com', array('mailto:')), 'Checking for "mailto:test@test.com"');
        $this->assertTrue(HaploValidate::is_url('http://test:test@test.com'), 'Checking for "http://test:test@test.com');
        $this->assertTrue(HaploValidate::is_url('https://www.test.com/'), 'Checking for "https://www.test.com/');
        $this->assertTrue(HaploValidate::is_url('http://www.test.com:80/'), 'Checking for "http://www.test.com:80/"');
    }

    public function test_is_not_url() {
        $this->assertFalse(HaploValidate::is_url('test'), 'Checking for "test"');
        $this->assertFalse(HaploValidate::is_url('file:///test/test.txt'), 'Checking for "file:///test/test.txt"');
        $this->assertFalse(HaploValidate::is_url('mailto:test@test.com'), 'Checking for "mailto:test@test.com');
    }

    public function test_is_hex_color() {
        $this->assertTrue(HaploValidate::is_hex_color('#ffffff'), 'Checking for "#ffffff"');
        $this->assertTrue(HaploValidate::is_hex_color('ffffff'), 'Checking for "ffffff"');
        $this->assertTrue(HaploValidate::is_hex_color('#fff'), 'Checking for "#fff"');
        $this->assertTrue(HaploValidate::is_hex_color('fff'), 'Checking for "fff"');
        $this->assertTrue(HaploValidate::is_hex_color('ffffff', true), 'Checking for "ffffff" with only 6 digit allowed');
        $this->assertTrue(HaploValidate::is_hex_color('ffffff', false, false), 'Checking for "ffffff" with # not allowed');
    }

    public function test_is_not_hex_color() {
        $this->assertFalse(HaploValidate::is_hex_color('#dfg'), 'Checking for "#dfg"');
        $this->assertFalse(HaploValidate::is_hex_color('#sjross'), 'Checking for "#sjross"');
        $this->assertFalse(HaploValidate::is_hex_color('dfg', 'Checking for "dfg"'));
        $this->assertFalse(HaploValidate::is_hex_color('sjrosss'), 'Checking for "sjrosss');
        $this->assertFalse(HaploValidate::is_hex_color('#fff', true), 'Checking for 6 digit only');
        $this->assertFalse(HaploValidate::is_hex_color('#fff', false, false), 'Checking for # not allowed');
    }

    public function test_is_en_day_of_week() {
        $this->assertTrue(HaploValidate::is_en_day_of_week('Monday'), 'Checking for "Monday"');
        $this->assertTrue(HaploValidate::is_en_day_of_week('Tuesday'), 'Checking for "Tuesday"');
        $this->assertTrue(HaploValidate::is_en_day_of_week('monday'), 'Checking for "monday"');
        $this->assertTrue(HaploValidate::is_en_day_of_week('MONDAY'), 'Checking for "MONDAY"');
    }

    public function test_is_not_en_day_of_week() {
        $this->assertFalse(HaploValidate::is_en_day_of_week('test'), 'Checking for "test"');
        $this->assertFalse(HaploValidate::is_en_day_of_week('Mondaytest'), 'Checking for "Mondaytest"');
    }

    public function test_is_en_abbr_day_of_week() {
        $this->assertTrue(HaploValidate::is_en_abbr_day_of_week('Mo'), 'Checking for "Mo"');
        $this->assertTrue(HaploValidate::is_en_abbr_day_of_week('Tu'), 'Checking for "Tu"');
        $this->assertTrue(HaploValidate::is_en_abbr_day_of_week('mo'), 'Checking for "mo"');
        $this->assertTrue(HaploValidate::is_en_abbr_day_of_week('MO'), 'Checking for "MO"');
    }

    public function test_is_not_en_abbr_day_of_week() {
        $this->assertFalse(HaploValidate::is_en_abbr_day_of_week('te'), 'Checking for "te"');
        $this->assertFalse(HaploValidate::is_en_abbr_day_of_week('Motest'), 'Checking for "Motest"');
    }
}