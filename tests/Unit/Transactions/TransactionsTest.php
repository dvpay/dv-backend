<?php

namespace Tests\Unit\Transactions;

 use App\Enums\CurrencyId;
 use App\Enums\TransactionType;
 use App\Models\HotWallet;
 use App\Models\Transaction;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Tests\TestCase;

 class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function testItRecalculatesHotWalletsOnNewExchangeTransaction()
    {
        $HotWalletAddress = md5('Expecto Patronum!');
        $HotWallet = HotWallet::factory([
            'currency_id' => CurrencyId::UsdtTron,
            'address'=> $HotWalletAddress,
            'blockchain' => CurrencyId::UsdtTron->getBlockchain(),
            'amount' => 0.00000000,
            'amount_usd' => 0.00000000,
        ])->create();


        Transaction::factory([
            'to_address' => $HotWallet->address,
            'type' => TransactionType::Exchange,
            'amount' => 50.00000000,
            'amount_usd'=> 50.00000000,
        ])->create();


        $this->assertEquals(0.00000000, $HotWallet->amount);
        $this->assertEquals(50.00000000, $HotWallet->fresh()->amount);
        $this->assertEquals(0.00000000, $HotWallet->amount_usd);
        $this->assertEquals(50.00000000, $HotWallet->fresh()->amount_usd);
    }


     public function testItRecalculatesHotWalletsOnNewInvoiceTransaction()
     {
         $HotWalletAddress = md5('Expecto Patronum!');
         $HotWallet = HotWallet::factory([
             'currency_id' => CurrencyId::UsdtTron,
             'address'=> $HotWalletAddress,
             'blockchain' => CurrencyId::UsdtTron->getBlockchain(),
             'amount' => 0.00000000,
             'amount_usd' => 0.00000000,
         ])->create();


         Transaction::factory([
             'to_address' => $HotWallet->address,
             'type' => TransactionType::Invoice,
             'amount' => 50.00000000,
             'amount_usd'=> 50.00000000,
         ])->create();


         $this->assertEquals(0.00000000, $HotWallet->amount);
         $this->assertEquals(50.00000000, $HotWallet->fresh()->amount);
         $this->assertEquals(0.00000000, $HotWallet->amount_usd);
         $this->assertEquals(50.00000000, $HotWallet->fresh()->amount_usd);
     }

    public function testItRecalculatesHotWalletsOnNewTransferTransaction()
    {
        $HotWalletAddress = md5('Expecto Patronum!');
        $HotWallet = HotWallet::factory([
            'currency_id' => CurrencyId::UsdtTron,
            'address'=> $HotWalletAddress,
            'blockchain' => CurrencyId::UsdtTron->getBlockchain(),
            'amount' => 0.00000000,
            'amount_usd' => 0.00000000,
        ])->create();


        Transaction::factory([
            'from_address' => $HotWallet->address,
            'type' => TransactionType::Transfer,
            'amount' => 50.00000000,
            'amount_usd'=> 50.00000000,
        ])->create();


        $this->assertEquals(0.00000000, $HotWallet->amount);
        $this->assertEquals(-50.00000000, $HotWallet->fresh()->amount);
        $this->assertEquals(0.00000000, $HotWallet->amount_usd);
        $this->assertEquals(-50.00000000, $HotWallet->fresh()->amount_usd);
    }


    public function testItRecalculatesHotWalletsOnSeveralNewTransactions()
    {
        $HotWalletAddress = md5('Expecto Patronum!');
        $HotWallet = HotWallet::factory([
            'currency_id' => CurrencyId::UsdtTron,
            'address'=> $HotWalletAddress,
            'blockchain' => CurrencyId::UsdtTron->getBlockchain(),
            'amount' => 0.00000000,
            'amount_usd' => 0.00000000,
        ])->create();

        Transaction::factory([
            'to_address' => $HotWallet->address,
            'type' => TransactionType::Exchange,
            'amount' => 10.00000000,
            'amount_usd'=> 10.00000000,
        ])->create();

        Transaction::factory([
            'to_address' => $HotWallet->address,
            'type' => TransactionType::Exchange,
            'amount' => 15.00000000,
            'amount_usd'=> 15.00000000,
        ])->create();

        Transaction::factory([
            'from_address' => $HotWallet->address,
            'type' => TransactionType::Transfer,
            'amount' => 13.00000001,
            'amount_usd'=> 13.00000000,
        ])->create();


        $this->assertEquals(0.00000000, $HotWallet->amount);
        $this->assertEquals(11.99999999, $HotWallet->fresh()->amount);
        $this->assertEquals(0.00000000, $HotWallet->amount_usd);
        $this->assertEquals(12.00000000, $HotWallet->fresh()->amount_usd);
    }

    public function testItRecalculatesHotWalletsOnSeveralNewTransactionsWithNotMatching()
    {
        $HotWalletAddress = md5('Expecto Patronum!');
        $HotWallet = HotWallet::factory([
            'currency_id' => CurrencyId::UsdtTron,
            'address'=> $HotWalletAddress,
            'blockchain' => CurrencyId::UsdtTron->getBlockchain(),
            'amount' => 0.00000000,
            'amount_usd' => 0.00000000,
        ])->create();

        Transaction::factory([
            'to_address' => $HotWallet->address,
            'type' => TransactionType::Exchange,
            'amount' => 10.00000000,
            'amount_usd'=> 10.00000000,
        ])->create();

        Transaction::factory([
            'to_address' => $HotWallet->address,
            'type' => TransactionType::Exchange,
            'amount' => 15.00000000,
            'amount_usd'=> 15.00000000,
        ])->create();

        Transaction::factory([
            'type' => TransactionType::Exchange,
            'amount' => 666.00000000,
            'amount_usd'=> 666.00000000,
        ])->create();

        Transaction::factory([
            'from_address' => $HotWallet->address,
            'type' => TransactionType::Transfer,
            'amount' => 13.00000001,
            'amount_usd'=> 13.00000000,
        ])->create();


        $this->assertEquals(0.00000000, $HotWallet->amount);
        $this->assertEquals(11.99999999, $HotWallet->fresh()->amount);
        $this->assertEquals(0.00000000, $HotWallet->amount_usd);
        $this->assertEquals(12.00000000, $HotWallet->fresh()->amount_usd);
    }
 }
