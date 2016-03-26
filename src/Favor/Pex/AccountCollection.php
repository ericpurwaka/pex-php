<?php namespace Favor\Pex;

use \Illuminate\Support;

class AccountCollection extends \Illuminate\Support\Collection
{

    public function __construct($creds)
    {
        $this->connection = new PexConnection($creds);

        $masterAccount = $this->connection->masterAccount();
        $accountList = $masterAccount["CHAccountList"];

        $items = array();
        foreach($accountList as $act) {
            $items[] = new Account($this->connection, $act);
        }

        parent::__construct($items);
    }



}