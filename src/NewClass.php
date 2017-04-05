<?php

use MyApp\PayModel;

class NewClass
{

    private $changeIt;
    private $good2;

    public function __construct(PayModel $changeIt, $good2)
    {

        $this->changeIt = $changeIt;
        $this->good2 = $good2;

    }

}
