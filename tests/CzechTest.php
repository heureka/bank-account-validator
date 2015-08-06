<?php
namespace tests;

use BankAccountValidator\Czech;

/**
 * CzechTest.php
 *
 * @author Jan Navratil <jan.navratil@heureka.cz>
 */
class CzechTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Czech
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new Czech();
    }

    /**
     * @param $expected
     * @param $accountNumber
     * @dataProvider parseNumberProvider
     */
    public function testParseNumber($expected, $accountNumber)
    {
        $this->assertSame($expected, $this->validator->parseNumber($accountNumber));
    }

    /**
     * @param $expected
     * @param $accountNumber
     * @dataProvider validateProvider
     */
    public function testValidation($expected, $accountNumber)
    {
        $this->assertSame($expected, $this->validator->validate($accountNumber));
    }


    public function parseNumberProvider()
    {
        return [
            [
                [null, null, null],
                "ASD"
            ],
            [
                [null, null, null],
                8
            ],
            [
                ['0', '12', '1234'],
                "0-12/1234"
            ],
            [
                [null, null, null],
                "0-2/1234"
            ],
            [
                //if one part is incorrect no matches returns
                [null, null, null],
                "21231221/123"
            ],
            [
                [null, null, null],
                "21231221/12312"
            ],
            [
                ['123456', '0000000123', '0000'],
                "123456-0000000123/0000"
            ],
            [
                [null, null, null],
                "123456-00000000123/0000"
            ],
            [
                [null, null, null],
                "1234567-000000123/0000"
            ]
        ];
    }

    public function validateProvider()
    {
        return [
            [
                false,
                '196437539/0100'
            ],
            [
                false,
                'asdasdasdasd'
            ],
            [
                true,
                '3055103/0300'
            ],
            [
                false,
                '3055103/30'
            ],
            [
                false,
                '30e55103/2200'
            ],
            [
                true,
                '0000-3055103/0300'
            ],
            [
                true,
                '0086-3055103/30'
            ],
        ];
    }
}