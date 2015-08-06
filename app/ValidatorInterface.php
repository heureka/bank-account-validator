<?php

namespace BankAccountValidator;

/**
 * ValidatorInterface.php
 *
 * @author Jan Navratil <jan.navratil@heureka.cz>
 */
interface ValidatorInterface
{

    /**
     * @param string $bankAccount - full bank account format (YYY-XXXXXXXX/ZZZZ)
     *
     * @return boolean
     */
    public function validate($bankAccount);
}