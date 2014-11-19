<?php namespace Favor\Pex;

class Account
{

    public $id;
    public $firstName;
    public $lastName;
    public $ledgerBalance;
    public $availableBalance;
    public $status;
    public $cards = [];

    private $connection;

    //*************************************************
    //********** Static Functions *********************
    //*************************************************

    public static function find($pexAccountId, $creds)
    {
        $connection = new PexConnection($creds);
        $account = $connection->findAccount($pexAccountId);
        if ($account) {
            return new self($connection, $account);
        }
    }

    //*************************************************
    //********** Public Member Functions *********************
    //*************************************************

    public function __construct($creds_or_conn, $account = null)
    {
        if (is_array($creds_or_conn)) {
            $this->connection = new PexConnection($creds_or_conn);
        } elseif (is_object($creds_or_conn) and $creds_or_conn instanceof PexConnection) {
            $this->connection = $creds_or_conn;
        } else {
            throw new \Exception('Cannot create Account Object without credentials of connection');
        }

        if ($account) {
            $this->fill($account);
        }
    }

    public function addFunds($amount)
    {
        if (is_numeric($amount) and $amount > 0) {
            $act = $this->connection->fund($this->id, $amount);
            $this->fill($act);
        }

        return $this;
    }

    public function removeFunds($amount)
    {
        if (is_numeric($amount) and $amount > 0) {
            $act = $this->connection->fund($this->id, -$amount);
            $this->fill($act);
        }

        return $this;
    }

    public function defundAccount()
    {
        //refersh account just before defunding
        $refreshedAccount = $this->connection->findAccount($this->id);

        $availableBalance = $refreshedAccount['availableBalance'];
        if ($availableBalance <= 0) {
            $this->fill($refreshedAccount);
        } else {
            $removeBalance = -$refreshedAccount['availableBalance'];
            $act = $this->connection->fund($this->id, $removeBalance);
            $this->fill($act);
        }

        return $this;
    }

    public function updateCardStatuses($newStatus)
    {
        $upperedStatus = strtoupper($newStatus);
        if (in_array($upperedStatus, Card::$updateableCardStatuses)) {

            $act = false;

            foreach($this->cards as $card) {
                if ($card->status != $upperedStatus and in_array($card->status, Card::$updateableCardStatuses)) {
                    $act = $this->connection->updateCardStatus($card->id, $upperedStatus);
                }
            }

            if ($act) {
                $this->fill($act);
            }
        }

        return $this;
    }

    //*************************************************
    //********** Protected Member Functions *********************
    //*************************************************

    protected function fill($account)
    {
        if ($account) {
            $this->id               = $account['id'];
            $this->firstName        = $account['firstName'];
            $this->lastName         = $account['lastName'];
            $this->ledgerBalance    = $account['ledgerBalance'];
            $this->availableBalance = $account['availableBalance'];
            $this->status           = $account['status'];

            $this->cards = [];
            foreach($account['cards'] as $c) {
                $this->cards[] = new Card($c);
            }
        }

        return $this;
    }

}