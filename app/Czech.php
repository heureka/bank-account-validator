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

    private $validBankCodes = [
          '0100' => 'Komerční banka, a.s.'
        , '0300' => 'Československá obchodní banka, a.s.'
        , '0600' => 'GE Money Bank, a.s.'
        , '0710' => 'Česká národní banka'
        , '0800' => 'Česká spořitelna, a.s.'
        , '2010' => 'Fio banka, a.s.'
        , '2020' => 'Bank of Tokyo-Mitsubishi UFJ (Holland) N.V. Prague Branch, organizační složka'
        , '2030' => 'AKCENTA, spořitelní a úvěrní družstvo'
        , '2060' => 'Citfin, spořitelní družstvo'
        , '2070' => 'Moravský Peněžní Ústav – spořitelní družstvo'
        , '2100' => 'Hypoteční banka, a.s.'
        , '2200' => 'Peněžní dům, spořitelní družstvo'
        , '2210' => 'ERB bank, a.s.'
        , '2220' => 'Artesa, spořitelní družstvo'
        , '2240' => 'Poštová banka, a.s., pobočka Česká republika'
        , '2250' => 'Záložna CREDITAS, spořitelní družstvo'
        , '2260' => 'ANO spořitelní družstvo'
        , '2310' => 'ZUNO BANK AG, organizační složka'
        , '2600' => 'Citibank Europe plc, organizační složka'
        , '2700' => 'UniCredit Bank Czech Republic and Slovakia, a.s.'
        , '3030' => 'Air Bank a.s.'
        , '3050' => 'BNP Paribas Personal Finance SA, odštěpný závod'
        , '3500' => 'ING Bank N.V.'
        , '4000' => 'Expobank CZ a.s.'
        , '4300' => 'Českomoravská záruční a rozvojová banka, a.s.'
        , '5400' => 'The Royal Bank of Scotland plc, organizační složka'
        , '5500' => 'Raiffeisenbank a.s.'
        , '5800' => 'J & T Banka, a.s.'
        , '6000' => 'PPF banka a.s.'
        , '6100' => 'Equa bank a.s.'
        , '6200' => 'COMMERZBANK Aktiengesellschaft, pobočka Praha'
        , '6210' => 'mBank S.A., organizační složka'
        , '6300' => 'BNP Paribas Fortis SA/NV, pobočka Česká republika'
        , '6700' => 'Všeobecná úverová banka a.s., pobočka Praha'
        , '6800' => 'Sberbank CZ, a.s.'
        , '7910' => 'Deutsche Bank A.G. Filiale Prag'
        , '7940' => 'Waldviertler Sparkasse Bank AG'
        , '7950' => 'Raiffeisen stavební spořitelna a.s.'
        , '7960' => 'Českomoravská stavební spořitelna, a.s.'
        , '7970' => 'Wüstenrot-stavební spořitelna a.s.'
        , '7980' => 'Wüstenrot hypoteční banka a.s.'
        , '7990' => 'Modrá pyramida stavební spořitelna, a.s.'
        , '8030' => 'Raiffeisenbank im Stiftland eG pobočka Cheb, odštěpný závod'
        , '8040' => 'Oberbank AG pobočka Česká republika'
        , '8060' => 'Stavební spořitelna České spořitelny, a.s.'
        , '8090' => 'Česká exportní banka, a.s.'
        , '8150' => 'HSBC Bank plc - pobočka Praha'
        , '8200' => 'PRIVAT BANK AG der Raiffeisenlandesbank Oberösterreich v České republice'
        , '8220' => 'Payment Execution s.r.o.'
        , '8230' => 'EEPAYS s.r.o.'
        , '8240' => 'Družstevní záložna Kredit'
    ];

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

        if (empty($parts)) {
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
}
