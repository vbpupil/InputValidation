<?php
/**
 * TestValidation.php Class
 *
 * @author: Dean Haines
 * @copyright: Dean Haines, 2018, UK
 * @license: GPL V3.0+ See LICENSE.md
 */


class TestValidation extends PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $sut;

    /**
     *
     */
    public function setup()
    {
        $this->sut = array(
            'name' => 'mike',
            'telephone' => '01604444555',
            'postcode' => 'nn12pw'
        );
    }

    /**
     *
     */
    public function testCheckInputsArrayOnValidate()
    {
        $r = \vbpupil\InputValidation::validate($this->sut, array());
        $this->assertEquals('no input names set, this is required to proceed', $r['error'][0]);
    }

    public function testValidation()
    {
        $r = \vbpupil\InputValidation::validate(array(
            'name' => 'mike',
            'telephone' => '01604444555',
            'postcode' => 'nn12pw'
        ), array('telephone'), true);

        $this->assertEquals($r['msg'][0], "[telephone|01604444555] identified as [uk_telephone] IS VALID");

    }

    /**
     *
     */
    public function testCheckRegex()
    {
        $this->assertEquals(false, \vbpupil\InputValidation::checkRegex('uk_telephone', '123'));
        $this->assertEquals(true, \vbpupil\InputValidation::checkRegex('uk_telephone', '+441604444555'));
    }

    /**
     *
     */
    public function testIdentify()
    {
        $this->assertEquals('uk_telephone', \vbpupil\InputValidation::identify('tel'));
        $this->assertEquals(false, \vbpupil\InputValidation::identify('oh-no'));
    }
}