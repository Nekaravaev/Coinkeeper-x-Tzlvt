<?php

namespace Nekaravaev\Coinkeeper\Tests\unit;

use Nekaravaev\Coinkeeper\Coinkeeper;
use Nekaravaev\Coinkeeper\Exceptions\CoinkeeperException;
use PHPUnit\Framework\TestCase;

class CoinkeeperTest extends TestCase
{
    protected $valid_credentials;
    protected $wrong_credentials;

    protected function setUp(): void
    {
        $this->valid_credentials =  [
            'user_id'  => PHPUNIT_USER_ID,
            'cookies'  => PHPUNIT_COOKIES,
            'budget'   => 1000
        ];

        $this->wrong_credentials = [
            'budget' => 1000,
            'cookies' => '',
            'user_id' => ''
        ];
    }

    public function totalToday( $pingResponse, $diff ) {
        $days_left = $pingResponse['total_days'] - $pingResponse['current_day'] + 1;
        $money_left = $this->getBudget() - $pingResponse['expenses_balance'] + $diff;

        return $money_left / $days_left;
    }

    public function testTotalToday() {

        $CoinKeeper = new Coinkeeper( $this->valid_credentials );

        $pingResponse = [
            'expenses_balance' => 800,
            'total_days' => 31,
            'current_day' => 29
        ];

        $diff = 100;

        $response = $CoinKeeper->totalToday( $pingResponse, $diff);

        $this->assertEquals(100, $response);
    }

    public function testPingWithWrongData() {
        $CoinKeeper = new Coinkeeper( $this->wrong_credentials );
        $this->expectException(CoinkeeperException::class);

        $CoinKeeper->ping();
    }

    public function testPingWithValidData() {
        $CoinKeeper = new Coinkeeper( $this->valid_credentials );

        $response = $CoinKeeper->ping();

        $keys = ['period_name', 'expenses_balance', 'current_day', 'total_days'];

        foreach ( $keys as $key )
            $this->assertArrayHasKey($key, $response, "Array doesn't contains {$key} as key");
    }

    public function testTransactionsWithWrongData() {
        $CoinKeeper = new Coinkeeper( $this->wrong_credentials );
        $this->expectException(CoinkeeperException::class);

        $CoinKeeper->transactions();
    }

    public function testTransactionsWithValidData() {
        $CoinKeeper = new Coinkeeper( $this->valid_credentials );

        $response = $CoinKeeper->transactions();

        $this->assertIsArray( $response );
    }

    public function testCalculateWithWrongData() {
        $CoinKeeper = new Coinkeeper( $this->wrong_credentials );
        $this->expectException(CoinkeeperException::class);

        $CoinKeeper->calculate();
    }

    public function testCalculateWithValidData() {
        $CoinKeeper = new Coinkeeper( $this->valid_credentials );

        $response = $CoinKeeper->calculate();

        $this->assertIsString( $response );
    }



}