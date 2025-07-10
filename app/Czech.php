<?php

namespace BankAccountValidator;

/**
 * Czech.php
 *
 * @author Jan Navratil <jan.navratil@heureka.cz>
 */
class Czech implements ValidatorInterface
{

    const FIRST_PART_MAX_DIGITS = 6;
    const SECOND_PART_MAX_DIGITS = 10;
    const SECOND_PART_MIN_DIGITS = 2;
    const BANK_CODE_LENGTH = 4;

    private $validBankCodes;

    /**
     * Defined scale for counting checksum
     * @var array
     */
    private $scale = [
        //order from right => scale
        0 => 1,
        1 => 2,
        2 => 4,
        3 => 8,
        4 => 5,
        5 => 10,
        6 => 9,
        7 => 7,
        8 => 3,
        9 => 6
    ];

    /**
     * @param string $cnfFile
     *
     * @throws MissingBankCodesFileException
     */
    public function __construct($cnfFile = __DIR__ . '/../cnf/czech-bank-codes.csv')
    {
        $this->validBankCodes = $this->getBankCodes($cnfFile);
    }

    /**
     * Use full valid format YYYY-XXXXXX/ZZZZ - prefix part is optional
     * Or use output from parseNumber method (array)
     * @param string|array $bankAccountNumber
     *
     * @return bool
     */
    public function validate($bankAccountNumber)
    {
        if (is_array($bankAccountNumber)) {
            $parts = $bankAccountNumber;
        } else {
            $parts = $this->parseNumber($bankAccountNumber);
        }

        if (empty(array_filter($parts))) {
            return false;
        }
        list($firstPart, $secondPart, $bankCode) = $parts;

        if (!$this->validateBankCode($bankCode)) {
            return false;
        }

        if (null !== $firstPart && !$this->validateCheckSum($firstPart)) {
            return false;
        }

        if (!$this->validateCheckSum($secondPart)) {
            return false;
        }

        return true;
    }

    /**
     * Count and verify checkSum based on legislative
     * @param string $bankAccountPart - numeric part from account number (first or second - before/after separator)
     *
     * @return bool
     */
    public function validateCheckSum($bankAccountPart)
    {
        $bankAccountPart = $this->trimZeroDigits($bankAccountPart);

        if (!empty($bankAccountPart)) {
            preg_match_all('/(\d)/', $bankAccountPart, $parts);
            if (!empty($parts) && !empty($parts[0])) {
                $parts = $parts[0];
                $sum = 0;
                $parts = array_reverse($parts);
                foreach ($parts as $order => $digit) {
                    if (!isset($this->scale[$order])) {
                        return false;
                    }
                    $sum += $this->scale[$order] * (int)$digit;
                }
                return 0 === $sum % 11;
            }

        } else {
            return true;
        }
        return false;
    }

    /**
     * @param string $bankCode
     *
     * @return bool
     */
    public function validateBankCode($bankCode)
    {
       return self::BANK_CODE_LENGTH == strlen($bankCode) && isset($this->validBankCodes[$bankCode]);
    }

    private function trimZeroDigits($number)
    {
        $number = preg_replace('/^(0{0,})/', '', $number);
        return $number;
    }

    /**
     * Parse string account number in format XXXX-YYYYYY/ZZZZ or XXXXXXX/ZZZZ
     * @param string $bankAccountNumber
     *
     * @return array [$firstPart, $secondPart, $bankCode] - if some part is not matched, return it null
     */
    public function parseNumber($bankAccountNumber)
    {
        $pattern = <<<PATTERN
            ~
            ^
            (?:
                ((\d{0,%1\$d})-(\d{%2\$d,%3\$d}))       # account number with two parts
                |                                       # or
                ((\d{%2\$d,%3\$d}))                     # single number
            )?
                \/                                      # slash for separate bank code
                (\d{%4\$d})                             # bank code
            $
            ~xim
PATTERN;

        $pattern = sprintf(
            $pattern,
            self::FIRST_PART_MAX_DIGITS,
            self::SECOND_PART_MIN_DIGITS,
            self::SECOND_PART_MAX_DIGITS,
            self::BANK_CODE_LENGTH
        );

        $firstPart = $secondPart = $bankCode = null;

        preg_match($pattern, $bankAccountNumber, $match);
        if (!empty($match[6])) {
            $bankCode = $match[6];
        }
        if (isset($match[2])
            && '' !== $match[2]
            && isset($match[3])
            && '' !== $match[3]) {
            $firstPart = $match[2];
            $secondPart = $match[3];
        } elseif (!empty($match[5])) {
            $secondPart = $match[5];
        }

        return [$firstPart, $secondPart, $bankCode];
    }

    /**
     * Returns all valid czech bank codes
     *
     * @return array
     */
    public function getValidBankCodes()
    {
        return $this->validBankCodes;
    }

    /**
     * @param $codesFile
     *
     * @return array
     * @throws MissingBankCodesFileException
     */
    private function getBankCodes($codesFile)
    {
        if (!is_file($codesFile)) {
            throw new MissingBankCodesFileException('Czech bank codes CSV file is not valid. ' . $codesFile);
        }
        $data = str_getcsv(file_get_contents($codesFile), "\n", '"', "");
        array_shift($data);
        $validBankCodes = [];
        foreach($data as &$row) {
            $row = str_getcsv($row, ";", '"', "");
            $validBankCodes[$row[0]] = $row[1];
        }
        return $validBankCodes;
    }

}
