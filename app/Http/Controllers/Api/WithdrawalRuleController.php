<?php

namespace App\Http\Controllers\Api;

use App\Enums\WithdrawalRuleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Withdrawal\WithdrawalIntervalRequest;
use App\Http\Resources\DefaultResponseResource;
use Illuminate\Contracts\Auth\Authenticatable;

class WithdrawalRuleController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index(Authenticatable $user): DefaultResponseResource
    {
        $withdrawalTypeList = WithdrawalRuleType::values();

        return DefaultResponseResource::make([
            'withdrawalTypeList'     => $withdrawalTypeList,
            'withdrawalRuleType'     => $user->settings->get('withdrawal_rule_type'),
            'withdrawalIntervalCron' => $user->settings->get('withdrawal_interval'),
            'withdrawalMinBalance'   => $user->settings->get('withdrawal_min_balance'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store(WithdrawalIntervalRequest $request, Authenticatable $user): DefaultResponseResource
    {
        $user->settings->set('withdrawal_interval', $request->input('withdrawalIntervalCron'));
        $user->settings->set('withdrawal_min_balance', $request->input('withdrawalMinBalance'));
        $user->settings->set('withdrawal_rule_type', $request->input('withdrawalRuleType'));

        $user->wallets()->update([
            'withdrawal_interval_cron' => $request->input('withdrawalIntervalCron'),
            'withdrawal_min_balance'   => $request->input('withdrawalMinBalance')
        ]);

        return DefaultResponseResource::make([]);
    }

}
