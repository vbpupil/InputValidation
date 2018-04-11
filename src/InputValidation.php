<?php
/**
 * InputValidation.php
 *
 * @author: Dean Haines
 * @copyright: Dean Haines, 2018, UK
 * @license: GPL V3.0+ See LICENSE.md
 */

namespace vbpupil;

use Exception;
use \Zend\Config\Reader\Yaml;

/**
 * Class InputValidation
 */
class InputValidation
{
    /**
     * @var
     */
    public static $config;

    /**
     * @var array
     */
    public static $regex = [
        'email' => "/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/",
        'postcode' => '~^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9]?[A-Za-z]))))[0-9][A-Za-z]{2})$~',
        'uk_telephone' => '~^(?:(?:\(?(?:0(?:0|11)\)?[\s-]?\(?|\+)44\)?[\s-]?(?:\(?0\)?[\s-]?)?)|(?:\(?0))(?:(?:\d{5}\)?[\s-]?\d{4,5})|(?:\d{4}\)?[\s-]?(?:\d{5}|\d{3}[\s-]?\d{3}))|(?:\d{3}\)?[\s-]?\d{3}[\s-]?\d{3,4})|(?:\d{2}\)?[\s-]?\d{4}[\s-]?\d{4}))(?:[\s-]?(?:x|ext\.?|\#)\d{3,4})?$~',
        'uk_mobile' => "/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/",
        'number' => "~^[0-9]+$~",
        'text' => "~^[a-z0-9\s]+$~i"
    ];

    /**
     * @param $data
     * @param $check
     * @return array
     * @throws Exception
     */
    public static function validate($data, $check)
    {
        try {
            self::getConfig();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        //1. contains results to be returned back to the client
        $results = [];
        $csrfChecked = false;

        if (empty($check)) {
            //we don't have any check inputs set, error and leave.
            $results['error'][] = 'no input names set, this is required to proceed';
            return $results;
        }

        //2. perform a token check before anything else
        try {
            if (isset(self::$config['config']['csrf_check']) && self::$config['config']['csrf_check'] == true) {
                if (self::compareToken($data['validation_token'], $data['form_id']) == true) {
                    $csrfChecked = true;
                } else {
                    $results['error'][] = [
                        'name' => 'CSRF',
                        'value' => '',
                        'type' => '',
                        'message' => 'CSRF VALIDATION FAILED, ARE SESSIONS ENABLED?.'
                    ];
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }


        foreach ($data as $k => $v) {
            $tmpETxt = self::$config['config']['error_text'];
            $tmpSTxt = self::$config['config']['success_text'];

            if ($k == 'validation_token' || $k == 'form_id') {
                continue;
            }

            if (self::notRequiredCheck($k)) {
                if (!empty($v)) {
                    $k = str_replace('*', '', $k);
                } else {
                    continue;
                }
            }

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
                        case 'uk_mobile':
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
                        $results['success'][] = [
                            'name' => $k,
                            'value' => $v,
                            'type' => $type,
                            'message' => str_replace(array('[NAME]', '[TYPE]', '[VALUE]'), array($k, $type, $v), $tmpSTxt)
                        ];
                    }

                    //oh no! we failed validation
                    if ($r == false) {
                        $results['error'][] = [
                            'name' => $k,
                            'value' => $v,
                            'type' => $type,
                            'message' => str_replace(array('[NAME]', '[TYPE]', '[VALUE]'), array($k, $type, $v), $tmpETxt)
                        ];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * if the name contains an * then if the value is empty don't complain
     * that were not valid
     *
     * @param $name
     * @return bool
     */
    public static function notRequiredCheck($name)
    {
        $r = (preg_match('~\*$~', $name) ? true : false);
        return $r;
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
     * @throws Exception
     */
    public static function identify($data)
    {
        foreach (self::getDefinitions() as $k => $v) {
            foreach ($v as $type) {
                if (strpos($type, $data) !== false) {
                    return str_replace("\r", '', $k);
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public static function getDefinitions()
    {
        $defs = [];
        foreach (self::$config['definitions'] as $k => $v) {
            if (!empty($v)) {
                $defs[$v][] = $k;
            }
        }
        return $defs;
    }

    /**
     * @throws Exception
     */
    public static function getConfig()
    {
        $reader = new \Zend\Config\Reader\Yaml(['Spyc', 'YAMLLoadString']);

        if (file_exists(dirname(dirname(dirname(dirname(__DIR__)))) . '/config/InputValidation/config.yml')) {
            self::$config = $reader->fromFile(dirname(dirname(dirname(dirname(__DIR__)))) . '/config/InputValidation/config.yml');
        } elseif (file_exists(__DIR__ . '/config/InputValidation/config.yml')) {
            self::$config = $reader->fromFile(__DIR__ . '/config/InputValidation/config.yml');
        } else {
            throw new Exception('Missing config file, cannot continue.');
        }

        foreach (array('error_text', 'success_text', 'csrf_check') as $check) {
            if (empty(self::$config['config']['error_text'])) {
                throw new Exception("{$check} in config is not set.");
            }
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function createToken($formID)
    {
        if (isset($formID) && $formID !== '' && is_string($formID)) {
            $token = self::generateToken($formID);
            return "<input type='hidden' name='validation_token' value='{$token}'><input type='hidden' name='form_id' value='{$formID}'>";
        }

        throw new Exception('Form Name is not valid');
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateToken($formID)
    {
        $token = bin2hex(random_bytes(16));
        $_SESSION['input_validation'] = array(hash('ripemd160', $formID) => $token);

        return $token;
    }

    /**
     * @param $input
     * @return bool
     * @throws Exception
     */
    private function compareToken($token, $formID)
    {
        if (
            array_key_exists(hash('ripemd160', $formID), $_SESSION['input_validation']) &&
            $_SESSION['input_validation'][hash('ripemd160', $formID)] == $token
        ) {
            return true;
        }
    }
}