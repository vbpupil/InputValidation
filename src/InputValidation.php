<?php
/**
 * InputValidation.php
 *
 * @author: Dean Haines
 * @copyright: Dean Haines, 2018, UK
 * @license: GPL V3.0+ See LICENSE.md
 */

namespace vbpupil;


/**
 * Class InputValidation
 */
class InputValidation
{
    /**
     * @var array
     */
    public static $regex = Array(
        'email' => "/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/",
        'postcode' => "~^(GIR 0AA)|(TDCU 1ZZ)|(ASCN 1ZZ)|(BIQQ 1ZZ)|(BBND 1ZZ)"
            . "|(FIQQ 1ZZ)|(PCRN 1ZZ)|(STHL 1ZZ)|(SIQQ 1ZZ)|(TKCA 1ZZ)"
            . "|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]"
            . "|[A-HK-Y][0-9]([0-9]|[ABEHMNPRV-Y]))"
            . "|[0-9][A-HJKS-UW])\\s?[0-9][ABD-HJLNP-UW-Z]{2}$~i",
        'uk_telephone' => '~^(?:(?:\(?(?:0(?:0|11)\)?[\s-]?\(?|\+)44\)?[\s-]?(?:\(?0\)?[\s-]?)?)|(?:\(?0))(?:(?:\d{5}\)?[\s-]?\d{4,5})|(?:\d{4}\)?[\s-]?(?:\d{5}|\d{3}[\s-]?\d{3}))|(?:\d{3}\)?[\s-]?\d{3}[\s-]?\d{3,4})|(?:\d{2}\)?[\s-]?\d{4}[\s-]?\d{4}))(?:[\s-]?(?:x|ext\.?|\#)\d{3,4})?$~',
        'uk_mobile' => "/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/"
    );

    /**
     * @param $data
     * @param $check
     * @return array
     * @throws \Exception
     */
    public static function validate($data, $check)
    {
        //contains results to be returned back to the client
        $results = array();

        if (empty($check)) {
            //we don't have any check inputs set, error and leave.
            $results['error'][] = 'no input names set, this is required to proceed';
            return $results;
        }

        //READ IN ERRO AND SUCCESS TEXT START
        try {
            $errorTxt = self::getErrorText();
        } catch (\Exception $e) {
            die(get_class() . ': ' . $e->getMessage());
        }

        try {
            $successTxt = self::getErrorText('success');
        } catch (\Exception $e) {
            die(get_class() . ': ' . $e->getMessage());
        }
        //READ IN ERRO AND SUCCESS TEXT END

        foreach ($data as $k => $v) {
            $tmpETxt = $errorTxt;
            //check that this input item is in the supplied array
            if (in_array($k, $check)) {
                //lets identify what this input type is
                $type = self::identify($k);

                if ($type == false) {
                    $results['error'][] = "[{$k}|{$v}] unable to identify type";
                }

                if ($type != false) {
                    switch ($type) {
                        case 'uk_telephone':
                            $v = str_replace(array('(', ')', ' '), '', $v);
                            $r = self::checkRegex($type, $v);
                            break;
                        case 'postcode':
                            $v = preg_replace('/\s+/', '', $v);
                            $r = self::checkRegex($type, $v);
                            break;
                        default:
                            $r = self::checkRegex($type, $v);
                            break;
                    }

                    //woohoo, we passed validation
                    if ($r == true) {
                        $results['msg'][] = "[{$k}|{$v}] identified as [{$type}] IS VALID";
                    }

                    //oh no! we failed validation
                    if ($r == false) {
                        $results['error'][] = str_replace(array('[NAME]', '[TYPE]','[VALUE]'), array($k,$type,$v), $tmpETxt);
                    }
                }
            }
        }


        return $results;
    }

    /**
     * runs the preg match and returns true if data passes validation, false if not
     *
     * @param $type
     * @param $value
     * @return bool
     */
    public static function checkRegex($type, $value)
    {
        return (bool)preg_match(self::$regex[$type], $value);
    }

    /**
     * identifies what type this input is, ie is it a tel or postcode
     * if we cannot identify, then we return as an anything.
     *
     * @param $data
     * @return int|string
     * @throws \Exception
     */
    public static function identify($data)
    {
        foreach (self::getDefinitions() as $k => $v) {
            foreach ($v as $type) {
                if (strpos($type, $data) !== false) {
                    return $k;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getDefinitions()
    {
        $defs = [];

        foreach (file(dirname(__DIR__) . '/src/config/definitions.txt') as $def) {
            if (!empty($def)) {
                $tmp = explode('|', str_replace("\n", '', $def));
                if (count($tmp) > 2) {
                    throw new \Exception('Invalid definitions file');
                }

                if ($tmp[0] == '' || $tmp[1] == '') {
                    throw new \Exception('Invalid definitions file');

                }

                $defs[$tmp[1]][] = $tmp[0];
            }
        }

        return $defs;
    }

    public static function getErrorText($type ='error')
    {
        if ($eTxt = file(dirname(__DIR__) . "/src/config/{$type}.txt")) {
            foreach ($eTxt AS $err) {
                if (!preg_match('~(^#|^\s)~', $err)) {
                    return $err;
                }
            }
        }

        throw new \Exception("{$type} config file not present");
    }
}