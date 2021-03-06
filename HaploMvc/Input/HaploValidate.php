<?php
namespace HaploMvc\Input;
 
/**
 * Class HaploValidate
 * @package HaploMvc
 */
class HaploValidate
{
    /**
     * @param string $pattern
     * @param mixed $input
     * @param int $min
     * @param int $max
     * @return int
     */
    protected static function isAlphaHelper($pattern, $input, $min = null, $max = null)
    {
        if (!is_null($min) && strlen($input) < $min) {
            return false;
        }
        if (!is_null($max) && strlen($input) > $max) {
            return false;
        }
        return preg_match('/^'.$pattern.'+$/i', $input);
    }

    /**
     * @param mixed $input
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function isAlpha($input, $min = null, $max = null)
    {
        return (bool)static::isAlphaHelper('[a-z0-9]', $input, $min, $max);
    }

    /**
     * @param mixed $input
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function isAlphaDashes($input, $min = null, $max = null)
    {
        return (bool)static::isAlphaHelper('[a-z0-9\s_-]', $input, $min, $max);
    }

    /**
     * @param mixed $input
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function isAlphaDashesQuotes($input, $min = null, $max = null)
    {
        return (bool)static::isAlphaHelper('[a-z0-9\s\'\"\._-]', $input, $min, $max);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isYesNo($input)
    {
        return in_array(strtolower($input), array('yes', 'no'));
    }

    /**
     * @param mixed $input
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function inRange($input, $min = null, $max = null)
    {
        if (is_numeric($input)) {
            return (($input >= $min || is_null($min)) && ($input <= $max || is_null($max)));
        }
        
        return false;
    }

    /**
     * @param mixed $input
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function hasLength($input, $min = null, $max = null)
    {
        return ((strlen($input) >= $min || is_null($min)) && (strlen($input) <= $max || is_null($max)));
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isEmail($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param mixed $input
     * @param array $allowedSchemes
     * @return bool
     */
    public static function isUrl($input, $allowedSchemes = array('http://', 'https://'))
    {
        $schemeOk = false;
        foreach ($allowedSchemes as $scheme) {
            if (substr(trim(strtolower($input)), 0, strlen($scheme)) === strtolower($scheme)) {
                $schemeOk = true;
                break;
            }
        }

        return $schemeOk && (bool)filter_var($input, FILTER_VALIDATE_URL);
    }

    /**
     * @param mixed $input
     * @param bool $sixDigitOnly
     * @param bool $allowHash
     * @return int
     */
    public static function isHexColor($input, $sixDigitOnly = false, $allowHash = true)
    {
        if ($sixDigitOnly) {
            return (bool)($allowHash ?
                preg_match('/^#?[0-9a-f]{6}$/i', $input) : preg_match('/^[0-9a-f]{6}$/i', $input));
        } else {
            return (bool)($allowHash ?
                preg_match('/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', $input)  : preg_match('/^([0-9a-f]{3}|[0-9a-f]{6})$/i', $input));
        }
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isEnDayOfWeek($input)
    {
        return (bool)preg_match('/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i', $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isEnAbbrDayOfWeek($input)
    {
        return (bool)preg_match('/^(Mo|Tu|We|Th|Fr|Sa|Su)$/i', $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isEnMonth($input)
    {
        return (bool)preg_match('/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)$/i', $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isEnAbbrMonth($input)
    {
        return (bool)preg_match('/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)$/i', $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isEnDigitWord($input)
    {
        return (bool)preg_match('/^(zero|one|two|three|four|five|six|seven|eight|nine)$/i', $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isIso3CountryCode($input)
    {
        return (bool)preg_match(
            '/^('.
            'ABW|AFG|AGO|AIA|ALA|ALB|AND|ARE|ARG|ARM|ASM|ATA|ATF|ATG|AUS|AUT|AZE|BDI|BEL|BEN|BES|BFA|BGD|'.
            'BGR|BHR|BHS|BIH|BLM|BLR|BLZ|BMU|BOL|BRA|BRB|BRN|BTN|BVT|BWA|CAF|CAN|CCK|CHE|CHL|CHN|CIV|CMR|'.
            'COD|COG|COK|COL|COM|CPV|CRI|CUB|CUW|CXR|CYM|CYP|CZE|DEU|DJI|DMA|DNK|DOM|DZA|ECU|EGY|ERI|ESH|'.
            'ESP|EST|ETH|FIN|FJI|FLK|FRA|FRO|FSM|GAB|GBR|GEO|GGY|GHA|GIB|GIN|GLP|GMB|GNB|GNQ|GRC|GRD|GRL|'.
            'GTM|GUF|GUM|GUY|HKG|HMD|HND|HRV|HTI|HUN|IDN|IMN|IND|IOT|IRL|IRN|IRQ|ISL|ISR|ITA|JAM|JEY|JOR|'.
            'JPN|KAZ|KEN|KGZ|KHM|KIR|KNA|KOR|KWT|LAO|LBN|LBR|LBY|LCA|LIE|LKA|LSO|LTU|LUX|LVA|MAC|MAF|MAR|'.
            'MCO|MDA|MDG|MDV|MEX|MHL|MKD|MLI|MLT|MMR|MNE|MNG|MNP|MOZ|MRT|MSR|MTQ|MUS|MWI|MYS|MYT|NAM|NCL|'.
            'NER|NFK|NGA|NIC|NIU|NLD|NOR|NPL|NRU|NZL|OMN|PAK|PAN|PCN|PER|PHL|PLW|PNG|POL|PRI|PRK|PRT|PRY|'.
            'PSE|PYF|QAT|REU|ROU|RUS|RWA|SAU|SDN|SEN|SGP|SGS|SHN|SJM|SLB|SLE|SLV|SMR|SOM|SPM|SRB|SSD|STP|'.
            'SUR|SVK|SVN|SWE|SWZ|SXM|SYC|SYR|TCA|TCD|TGO|THA|TJK|TKL|TKM|TLS|TON|TTO|TUN|TUR|TUV|TWN|TZA|'.
            'UGA|UKR|UMI|URY|USA|UZB|VAT|VCT|VEN|VGB|VIR|VNM|VUT|WLF|WSM|YEM|ZAF|ZMB|ZWE'.
            ')$/i'
        , $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isIso2CountryCode($input)
    {
        return (bool)preg_match(
            '/^('.
            'AD|AE|AF|AG|AI|AL|AM|AO|AQ|AR|AS|AT|AU|AW|AX|AZ|BA|BB|BD|BE|BF|BG|BH|BI|BJ|BL|BM|BN|BO|BQ|BQ|'.
            'BR|BS|BT|BV|BW|BY|BZ|CA|CC|CD|CF|CG|CH|CI|CK|CL|CM|CN|CO|CR|CU|CV|CW|CX|CY|CZ|DE|DJ|DK|DM|DO|'.
            'DZ|EC|EE|EG|EH|ER|ES|ET|FI|FJ|FK|FM|FO|FR|GA|GB|GD|GE|GF|GG|GH|GI|GL|GM|GN|GP|GQ|GR|GS|GT|GU|'.
            'GW|GY|HK|HM|HN|HR|HT|HU|ID|IE|IL|IM|IN|IO|IQ|IR|IS|IT|JE|JM|JO|JP|KE|KG|KH|KI|KM|KN|KP|KR|KW|'.
            'KY|KZ|LA|LB|LC|LI|LK|LR|LS|LT|LU|LV|LY|MA|MC|MD|ME|MF|MG|MH|MK|ML|MM|MN|MO|MP|MQ|MR|MS|MT|MU|'.
            'MV|MW|MX|MY|MZ|NA|NC|NE|NF|NG|NI|NL|NO|NP|NR|NU|NZ|OM|PA|PE|PF|PG|PH|PK|PL|PM|PN|PR|PS|PT|PW|'.
            'PY|QA|RE|RO|RS|RU|RW|SA|SB|SC|SD|SE|SG|SH|SI|SJ|SK|SL|SM|SN|SO|SR|SS|ST|SV|SX|SY|SZ|TC|TD|TF|'.
            'TG|TH|TJ|TK|TL|TM|TN|TO|TR|TT|TV|TW|TZ|UA|UG|UM|US|UY|UZ|VA|VC|VE|VG|VI|VN|VU|WF|WS|YE|YT|ZA|'.
            'ZM|ZW'.
            ')$/i'
        , $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isUsStateCode($input)
    {
        return (bool)preg_match(
            '/^('.
            'AE|AL|AK|AP|AS|AZ|AR|CA|CO|CT|DE|DC|FM|FL|GA|GU|HI|ID|IL|IN|IA|KS|KY|LA|ME|MH|MD|MA|MI|MN|MS|'.
            'MO|MP|MT|NE|NV|NH|NJ|NM|NY|NC|ND|OH|OK|OR|PW|PA|PR|RI|SC|SD|TN|TX|UT|VT|VI|VA|WA|WV|WI|WY'.
            ')$/i'
        , $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isUsZip($input)
    {
        // source - https://www.owasp.org/index.php/OWASP_Validation_Regex_Repository
        return (bool)preg_match('/^\d{5}(-\d{4})?$/', $input);
    }

    /**
     * @param mixed $input
     * @return int
     */
    public static function isUkPostcode($input)
    {
        // source - http://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom
        // not perfect but good enough to match all valid postcodes (may match a few invalid ones also)
        // modified to allow no spaces or more than one space between the two postcode parts
        return (bool)preg_match('/^[A-Z]{1,2}[0-9R][0-9A-Z]?\s[0-9][ABD-HJLNP-UW-Z]{2}$/i', $input);
    }

    /**
     * @param mixed $input
     * @param string $separator
     * @return bool
     */
    public static function isReversedDate($input, $separator = '/')
    {
        // format - yyyy/mm/dd
        if (
            !preg_match("#^(?<year>[0-9]{4})$separator(?<month>[0-9]{1,2})$separator(?<day>[0-9]{1,2})$#", $input, $matches) || 
            !strtotime($input) ||
            ($matches['month'] == 2 && $matches['day'] == 29 && !date('L', strtotime($input))) // check for 29th in non-leap year
        ) {
            return false;
        }
        
        return true;
    }

    /**
     * @param mixed $input
     * @param string $separator
     * @return bool
     */
    public static function isUsDate($input, $separator = '/')
    {
        // format mm/dd/yyyy
        if (
            !preg_match("#^(?<month>[0-9]{1,2})$separator(?<day>[0-9]{1,2})$separator(?<year>[0-9]{4})$#", $input, $matches) || 
            !strtotime($input) ||
            ($matches['month'] == 2 && $matches['day'] == 29 && !date('L', strtotime($input))) // check for 29th in non-leap year
        ) {
            return false;
        }
        
        return true;
    }

    /**
     * @param mixed $input
     * @param string $separator
     * @return bool
     */
    public static function isUkDate($input, $separator = '/')
    {
        // format dd/mm/yyyy
        if (
            !preg_match("#^(?<day>[0-9]{1,2})$separator(?<month>[0-9]{1,2})$separator(?<year>[0-9]{4})$#", $input, $matches) || 
            !strtotime($matches['year'].$separator.$matches['month'].$separator.$matches['day']) ||
            ($matches['month'] == 2 && $matches['day'] == 29 && !date('L', strtotime($input))) // check for 29th in non-leap year
        ) {
            return false;
        }
        
        return true;
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isIp($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_IP);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isIpv4($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isIpv6($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isPrivateIp($input)
    {
        return !filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isReservedIp($input)
    {
        return !filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isPublicIp($input)
    {
        return !self::isPrivateIp($input) && !self::isReservedIp($input);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isGender($input)
    {
        return in_array(strtolower($input), array('male', 'female'));
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isEnLetter($input)
    {
        return in_array(strtolower($input), range('a', 'z'));
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public static function isEmptyString($input)
    {
        return trim($input) == '';
    }
}
