<?php
namespace Nekaravaev\Coinkeeper;

use Nekaravaev\Coinkeeper\Exceptions\CoinkeeperException;
use Requests;

class Coinkeeper {

    /** @var integer */
    private $_budget;

    /** @var string */
    private $_cookies;

    /** @var string */
    private $_user_id;

    /** @var array */
    protected $possible_source_types = [2];

    /** @var array */
    protected $possible_destincation_types = [3];


    /**
     * @var array $config
     * [
     *  'user_id' => '...',
     *  'cookies' => '...',
     *  'budget' => 100
     *  ]
     */
    public function __construct(array $config)
    {
        $this->_user_id = $config['user_id'];
        $this->_cookies = $config['cookies'];
        $this->_budget = $config['budget'];

        return $this;
    }

    /**
     * @return int
     */
    public function getBudget(): int
    {
        return (int) $this->_budget;
    }

    /**
     * @return string
     */
    public function getCookies(): string
    {
        return $this->_cookies;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->_user_id;
    }

    public function ping() {
        $request = Requests::post(Endpoints::$ping,
            ['Accept' => 'application/json',
             'Content-Type' => 'application/json',
             'Cookie' => $this->getCookies() ],
            '{"items":[{"key":0,"entityJson":null},{"key":1,"entityJson":null},{"key":2,"entityJson":null},{"key":3,"entityJson":null},{"key":4,"entityJson":null},{"key":5,"entityJson":null},{"key":6,"entityJson":null}]}'
        );
        $response = json_decode(  $request->body );

        if ( empty( $response->data->items ) )
            throw new CoinkeeperException('Can\'t ping coinkeeper');

        $period_info = json_decode($response->data->items[5]->entityJson);

        return [
            'period_name' => $period_info->period,
            'expenses_balance' => round( $period_info->expenseSpentBalance ),
            'current_day' => (int) $period_info->currentNumberOfDaysInPeriod,
            'total_days' => (int) $period_info->totalNumberOfDaysInPeriod
        ];
    }

    public function transactions() {
        $request = Requests::post(Endpoints::$transaction_get,
            ['Accept' => 'application/json',
             'Content-Type' => 'application/json',
             'Cookie' => $this->getCookies() ],
            '{"userId":"'. $this->getUserId() .'","skip":0,"take":40,"categoryIds":[],"tagIds":[],"period":{}}'
        );

        $response = json_decode( $request->body );

        if ( empty( $response->transactions ) )
            throw new CoinkeeperException('Can\'t fetch list of transactions');

        $transactions_list = [];
        foreach ( $response->transactions as $transaction ) {
            if ( in_array( $transaction->sourceType, $this->possible_source_types)
                 && in_array( $transaction->destinationType, $this->possible_destincation_types) ) {

                $amount = $transaction->destinationAmount;
                $dateTimestamp = $transaction->dateTimestampISO;
                $dateTime = new \DateTime( $dateTimestamp );

                $dayInMonth = (int) $dateTime->format('j');

                $transactions_list[] = [ 'amount' => $amount, 'dayInMonth' => $dayInMonth ];
            }
        }

        return $transactions_list;
    }

    public function totalToday( $pingResponse, $diff ) {
        $days_left = $pingResponse['total_days'] - $pingResponse['current_day'] + 1;
        $money_left = $this->getBudget() - $pingResponse['expenses_balance'] + $diff;

        return $money_left / $days_left;
    }

    public function calculate() {
        $pingResponse = $this->ping();
        $transactions = $this->transactions();


        $diff = array_reduce( $transactions, function ( $carry, $transaction) use ($pingResponse) {
            if ( $transaction['dayInMonth'] === $pingResponse['current_day'] ) {
                return $carry + $transaction['amount'];
            }
            return $carry;
        }, 0);

        $totalAvailableToday = $this->totalToday($pingResponse, $diff);
        $availableNow        = $totalAvailableToday - $diff;
        return "Total: $totalAvailableToday Available: $availableNow";
    }

}