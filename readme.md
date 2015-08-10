[![Build Status](https://travis-ci.org/heureka/bank-account-validator.svg?branch=master)](https://travis-ci.org/heureka/bank-account-validator)

Basic account number validation based on checksum for specific countries
---------

Use make file for run test. `make tests`

Usage
-----

Install with [composer](https://getcomposer.org/):

```bash
composer require heureka/bankAccountValidator
```

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$validator = new BankAccountValidator\Czech();
$isValid = $validator->validate('333-123/0123');

```

Implemented Countries
---------------------

First implemented country is Czech Republic

Czech validation part is based on law 169/2011 Sb. [1]  
For validate correct bank code is used table from cnb.cz [2]  
 

Links
-----
 
[1] http://www.cnb.cz/cs/platebni_styk/ucty_kody_bank/download/kody_bank_CR.pdf  
[2] http://www.cnb.cz/cs/platebni_styk/pravni_predpisy/download/vyhl_169_2011.pdf


License
-------

GPL 2.1
