<?php namespace Favor\Pex;

class Card
{

    public $id;
    public $cardNumber;
    public $status;
    public $expires_at;
    public $issued_at;

    protected $connection;

    public static $updateableCardStatuses = array('BLOCKED', 'ACTIVE');
    public static $validNewStatuses = array('ACTIVE', 'BLOCKED');

    public function __construct($creds_or_conn, $card = NULL)
    {
        if (is_array($creds_or_conn)) {
            $this->connection = new PexConnection($creds_or_conn);
        } elseif (is_object($creds_or_conn) and $creds_or_conn instanceof PexConnection) {
            $this->connection = $creds_or_conn;
        } else {
            throw new \Exception('Cannot create Pex Card Object without credentials of connection');
        }

        if ($card) {
            $this->fill($card);
        }
    }

    public function updateStatus($status)
    {
        $upperedStatus = strtoupper($status);
        if (in_array($upperedStatus, Card::$validNewStatuses)) {
            if ($this->status != $upperedStatus and in_array($this->status, Card::$updateableCardStatuses)) {
                $act = $this->connection->updateCardStatus($this->id, $upperedStatus);
                if ($act) {
                    $this->status = $upperedStatus;
                }
            }
        }
    }

    //*************************************************
    //********** Protected Member Functions *********************
    //*************************************************

    /**
     * @param $cardArray
     *
     * v4 spec
     * {
     *   "CardId": 0,
     *   "IssuedDate": "",
     *   "ExpirationDate": "",
     *   "Last4CardNumber": "",
     *   "CardStatus": ""
     *   }
     */
    protected function fill($cardArray)
    {
        if ($cardArray) {
            $this->id           = $cardArray['CardId'];
            $this->cardNumber   = $cardArray['Last4CardNumber'];
            $this->status       = $cardArray['CardStatus'];
            $this->expires_at   = $cardArray['ExpirationDate'];
            $this->issued_at    = $cardArray['IssuedDate'];
        }
    }

}
