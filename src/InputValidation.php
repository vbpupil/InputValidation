<?php
/**
 * InputValidation.php
 *
 * @author: Dean Haines
 * @copyright: Dean Haines, 2018, UK
 * @license: GPL V3.0+ See LICENSE.md
 */

namespace vbpupil;


class InputValidation
{
    public static $definitions = Array(
        'email' => "/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/",
        'postcode' => "~^(GIR 0AA)|(TDCU 1ZZ)|(ASCN 1ZZ)|(BIQQ 1ZZ)|(BBND 1ZZ)"
            . "|(FIQQ 1ZZ)|(PCRN 1ZZ)|(STHL 1ZZ)|(SIQQ 1ZZ)|(TKCA 1ZZ)"
            . "|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]"
            . "|[A-HK-Y][0-9]([0-9]|[ABEHMNPRV-Y]))"
            . "|[0-9][A-HJKS-UW])\\s?[0-9][ABD-HJLNP-UW-Z]{2}$~i",

        'phone' => '!^\(?(?:(?:0(?:0|11)\)?[\s-]?\(?|\+)44\)?[\s-]?\(?(?:0\)?[\s-]?\(?)?|0)(?:\d{2}\)?[\s-]?\d{4}[\s-]?\d{4}|\d{3}\)?[\s-]?\d{3}[\s-]?\d{3,4}|\d{4}\)?[\s-]?(?:\d{5}|\d{3}[\s-]?\d{3})|\d{5}\)?[\s-]?\d{4,5}|8(?:00[\s-]?11[\s-]?11|45[\s-]?46[\s-]?4\d))(?:(?:[\s-]?(?:x|ext\.?\s?|\#)\d+)?)$!',


        'date' => "^[0-9]{4}[-/][0-9]{1,2}[-/][0-9]{1,2}\$",
        'amount' => "^[-]?[0-9]+\$",
        'number' => "^[-]?[0-9,]+\$",
        'alfanum' => "^[0-9a-zA-Z ,.-_\\s\?\!]+\$",
        'not_empty' => "[a-z0-9A-Z]+",
        'words' => "^[A-Za-z]+[A-Za-z \\s]*\$",
        'plate' => "^([0-9a-zA-Z]{2}[-]){2}[0-9a-zA-Z]{2}\$",
        'price' => "^[0-9.,]*(([.,][-])|([.,][0-9]{2}))?\$",
        '2digitopt' => "^\d+(\,\d{2})?\$",
        '2digitforce' => "^\d+\,\d\d\$",
        'anything' => "^[\d\D]{1,}\$"
    );

    public static $lookup = array(
        'email' => array('contact_email', 'sales_email'),
        'telephone' => array('phone', 'tel', 'telephone'),
        'postcode' => array('postcode', 'post-code', 'post_code')
    );

    public static function validate($data)
    {
        foreach ($data as $k => $v) {
            switch ($type = self::identify($k)) {
                case 'telephone':
                    $v = str_replace(array('(', ')', ' '), '', $v);
                    $r = self::checkRegex(self::identify($k), $v);
                    break;
                default:
                    $r = self::checkRegex(self::identify($k), $v);
                    break;
            }

            if ($r == true) {
                echo '<span style="color:green">';
                echo "[{$k}] identified as [{$type}] VALID {$v}<br>";
                echo '</span>';
            }

            if ($r == false) {
                echo '<span style="color:red">';
                echo "[{$k}] identified as [{$type}] INVALID {$v}<br>";
                echo '</span>';
            }
        }
    }

    public static function checkRegex($type, $value)
    {
        return (bool)preg_match(self::$definitions[$type], $value);
    }

    public static function identify($data)
    {
        foreach (self::$lookup as $k => $v) {
            foreach ($v as $type) {
                if (strpos($type, $data) !== false) {
                    return $k;
                }
            }
        }

        return 'assuming text';
    }
}