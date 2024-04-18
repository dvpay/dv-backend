<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\ServiceLogHistoryDto;
use App\Http\Resources\Dashboard\GetFinancialStatsResponseResource;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Heartbeat\HeartbeatServiceCollection;
use App\Http\Resources\Heartbeat\ServiceLogHistoryCollection;
use App\Http\Resources\Heartbeat\ServiceLogLaunchCollection;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Heartbeat\HeartbeatService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;
use OpenApi\Attributes as OA;

/**
 * HeartbeatController
 * todo write policy for this controller
 */
class HeartbeatController extends ApiController
{
    /**
     * @param HeartbeatService $heartbeatService
     */
    public function __construct(private readonly HeartbeatService $heartbeatService)
    {
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     * @throws InvalidArgumentException
     */
    public function getStatus(Request $request): DefaultResponseResource
    {
        $status = $this->heartbeatService->getSystemStatus();

        return new DefaultResponseResource($status);
    }


    /**
     * @param Request $request
     * @return DefaultResponseResource
     */
    #[OA\Get(
        path: '/stores/heartbeat/status',
        summary: 'Get dictionaries',
        security: [["bearerAuth" => []]],
        tags: ['Heartbeat'],
        responses: [
            new OA\Response(response: 200, description: "Get static address", content: new OA\JsonContent(
                example: '{"result":{"currencies":[{"id":"BTC.Bitcoin","code":"BTC","name":"BTC","precision":8,"isFiat":false,"contractAddress":""},{"id":"ETH.Ethereum","code":"ETH","name":"ETH","precision":6,"isFiat":false,"contractAddress":""},{"id":"TRX.Tron","code":"TRX","name":"TRX","precision":6,"isFiat":false,"contractAddress":""},{"id":"USD","code":"USD","name":"USD","precision":2,"isFiat":true,"contractAddress":""},{"id":"USDT.ETH","code":"USDT","name":"USDT","precision":2,"isFiat":false,"contractAddress":"0xdAC17F958D2ee523a2206206994597C13D831ec7"},{"id":"USDT.Tron","code":"USDT","name":"USDT","precision":2,"isFiat":false,"contractAddress":"TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t"}],"rateSources":["Binance","CoinGate"],"webhookTypes":{"InvoiceCreated":"Anewinvoicehasbeencreated","PaymentReceived":"Anewpaymenthasbeenreceived","InvoiceExpired":"Aninvoicehasexpired"},"blockchains":{"bitcoin":{"name":"bitcoin","title":"Bitcoin","nativeToken":"BTC","importMethods":["address","mnemonic"],"currencies":["BTC"]},"ethereum":{"name":"ethereum","title":"Ethereum","nativeToken":"ETH","importMethods":["address","mnemonic"],"currencies":["ETH","USDT"]},"tron":{"name":"tron","title":"Tron","nativeToken":"TRX","importMethods":["address","mnemonic"],"currencies":["TRX","USDT"]}},"invoiceStatuses":["Waiting","Waitingconfirmations","Paid","Partiallypaid","Partiallypaidexpired","Expired","Canceled","Success","Overpaid"],"withdrawalIntervals":{"Never":"\u041d\u0438\u043a\u043e\u0433\u0434\u0430","EveryOneMin":"\u041a\u0430\u0436\u0434\u0443\u044e\u043c\u0438\u043d\u0443\u0442\u0443","Every12hours":"\u041a\u0430\u0436\u0434\u044be12\u0447\u0430\u0441\u043e\u0432","EveryDay":"\u041a\u0430\u0436\u0434\u044b\u0439\u0434\u0435\u043d\u044c","Every3Days":"\u041a\u0430\u0436\u0434\u044b\u04353\u0434\u043d\u044f","EveryWeek":"\u041a\u0430\u0436\u0434\u0443\u044e\u043d\u0435\u0434\u0435\u043b\u044e"},"locations":["Africa\/Abidjan","Africa\/Accra","Africa\/Addis_Ababa","Africa\/Algiers","Africa\/Asmara","Africa\/Bamako","Africa\/Bangui","Africa\/Banjul","Africa\/Bissau","Africa\/Blantyre","Africa\/Brazzaville","Africa\/Bujumbura","Africa\/Cairo","Africa\/Casablanca","Africa\/Ceuta","Africa\/Conakry","Africa\/Dakar","Africa\/Dar_es_Salaam","Africa\/Djibouti","Africa\/Douala","Africa\/El_Aaiun","Africa\/Freetown","Africa\/Gaborone","Africa\/Harare","Africa\/Johannesburg","Africa\/Juba","Africa\/Kampala","Africa\/Khartoum","Africa\/Kigali","Africa\/Kinshasa","Africa\/Lagos","Africa\/Libreville","Africa\/Lome","Africa\/Luanda","Africa\/Lubumbashi","Africa\/Lusaka","Africa\/Malabo","Africa\/Maputo","Africa\/Maseru","Africa\/Mbabane","Africa\/Mogadishu","Africa\/Monrovia","Africa\/Nairobi","Africa\/Ndjamena","Africa\/Niamey","Africa\/Nouakchott","Africa\/Ouagadougou","Africa\/Porto-Novo","Africa\/Sao_Tome","Africa\/Tripoli","Africa\/Tunis","Africa\/Windhoek","America\/Adak","America\/Anchorage","America\/Anguilla","America\/Antigua","America\/Araguaina","America\/Argentina\/Buenos_Aires","America\/Argentina\/Catamarca","America\/Argentina\/Cordoba","America\/Argentina\/Jujuy","America\/Argentina\/La_Rioja","America\/Argentina\/Mendoza","America\/Argentina\/Rio_Gallegos","America\/Argentina\/Salta","America\/Argentina\/San_Juan","America\/Argentina\/San_Luis","America\/Argentina\/Tucuman","America\/Argentina\/Ushuaia","America\/Aruba","America\/Asuncion","America\/Atikokan","America\/Bahia","America\/Bahia_Banderas","America\/Barbados","America\/Belem","America\/Belize","America\/Blanc-Sablon","America\/Boa_Vista","America\/Bogota","America\/Boise","America\/Cambridge_Bay","America\/Campo_Grande","America\/Cancun","America\/Caracas","America\/Cayenne","America\/Cayman","America\/Chicago","America\/Chihuahua","America\/Ciudad_Juarez","America\/Costa_Rica","America\/Creston","America\/Cuiaba","America\/Curacao","America\/Danmarkshavn","America\/Dawson","America\/Dawson_Creek","America\/Denver","America\/Detroit","America\/Dominica","America\/Edmonton","America\/Eirunepe","America\/El_Salvador","America\/Fort_Nelson","America\/Fortaleza","America\/Glace_Bay","America\/Goose_Bay","America\/Grand_Turk","America\/Grenada","America\/Guadeloupe","America\/Guatemala","America\/Guayaquil","America\/Guyana","America\/Halifax","America\/Havana","America\/Hermosillo","America\/Indiana\/Indianapolis","America\/Indiana\/Knox","America\/Indiana\/Marengo","America\/Indiana\/Petersburg","America\/Indiana\/Tell_City","America\/Indiana\/Vevay","America\/Indiana\/Vincennes","America\/Indiana\/Winamac","America\/Inuvik","America\/Iqaluit","America\/Jamaica","America\/Juneau","America\/Kentucky\/Louisville","America\/Kentucky\/Monticello","America\/Kralendijk","America\/La_Paz","America\/Lima","America\/Los_Angeles","America\/Lower_Princes","America\/Maceio","America\/Managua","America\/Manaus","America\/Marigot","America\/Martinique","America\/Matamoros","America\/Mazatlan","America\/Menominee","America\/Merida","America\/Metlakatla","America\/Mexico_City","America\/Miquelon","America\/Moncton","America\/Monterrey","America\/Montevideo","America\/Montserrat","America\/Nassau","America\/New_York","America\/Nome","America\/Noronha","America\/North_Dakota\/Beulah","America\/North_Dakota\/Center","America\/North_Dakota\/New_Salem","America\/Nuuk","America\/Ojinaga","America\/Panama","America\/Paramaribo","America\/Phoenix","America\/Port-au-Prince","America\/Port_of_Spain","America\/Porto_Velho","America\/Puerto_Rico","America\/Punta_Arenas","America\/Rankin_Inlet","America\/Recife","America\/Regina","America\/Resolute","America\/Rio_Branco","America\/Santarem","America\/Santiago","America\/Santo_Domingo","America\/Sao_Paulo","America\/Scoresbysund","America\/Sitka","America\/St_Barthelemy","America\/St_Johns","America\/St_Kitts","America\/St_Lucia","America\/St_Thomas","America\/St_Vincent","America\/Swift_Current","America\/Tegucigalpa","America\/Thule","America\/Tijuana","America\/Toronto","America\/Tortola","America\/Vancouver","America\/Whitehorse","America\/Winnipeg","America\/Yakutat","Antarctica\/Casey","Antarctica\/Davis","Antarctica\/DumontDUrville","Antarctica\/Macquarie","Antarctica\/Mawson","Antarctica\/McMurdo","Antarctica\/Palmer","Antarctica\/Rothera","Antarctica\/Syowa","Antarctica\/Troll","Antarctica\/Vostok","Arctic\/Longyearbyen","Asia\/Aden","Asia\/Almaty","Asia\/Amman","Asia\/Anadyr","Asia\/Aqtau","Asia\/Aqtobe","Asia\/Ashgabat","Asia\/Atyrau","Asia\/Baghdad","Asia\/Bahrain","Asia\/Baku","Asia\/Bangkok","Asia\/Barnaul","Asia\/Beirut","Asia\/Bishkek","Asia\/Brunei","Asia\/Chita","Asia\/Choibalsan","Asia\/Colombo","Asia\/Damascus","Asia\/Dhaka","Asia\/Dili","Asia\/Dubai","Asia\/Dushanbe","Asia\/Famagusta","Asia\/Gaza","Asia\/Hebron","Asia\/Ho_Chi_Minh","Asia\/Hong_Kong","Asia\/Hovd","Asia\/Irkutsk","Asia\/Jakarta","Asia\/Jayapura","Asia\/Jerusalem","Asia\/Kabul","Asia\/Kamchatka","Asia\/Karachi","Asia\/Kathmandu","Asia\/Khandyga","Asia\/Kolkata","Asia\/Krasnoyarsk","Asia\/Kuala_Lumpur","Asia\/Kuching","Asia\/Kuwait","Asia\/Macau","Asia\/Magadan","Asia\/Makassar","Asia\/Manila","Asia\/Muscat","Asia\/Nicosia","Asia\/Novokuznetsk","Asia\/Novosibirsk","Asia\/Omsk","Asia\/Oral","Asia\/Phnom_Penh","Asia\/Pontianak","Asia\/Pyongyang","Asia\/Qatar","Asia\/Qostanay","Asia\/Qyzylorda","Asia\/Riyadh","Asia\/Sakhalin","Asia\/Samarkand","Asia\/Seoul","Asia\/Shanghai","Asia\/Singapore","Asia\/Srednekolymsk","Asia\/Taipei","Asia\/Tashkent","Asia\/Tbilisi","Asia\/Tehran","Asia\/Thimphu","Asia\/Tokyo","Asia\/Tomsk","Asia\/Ulaanbaatar","Asia\/Urumqi","Asia\/Ust-Nera","Asia\/Vientiane","Asia\/Vladivostok","Asia\/Yakutsk","Asia\/Yangon","Asia\/Yekaterinburg","Asia\/Yerevan","Atlantic\/Azores","Atlantic\/Bermuda","Atlantic\/Canary","Atlantic\/Cape_Verde","Atlantic\/Faroe","Atlantic\/Madeira","Atlantic\/Reykjavik","Atlantic\/South_Georgia","Atlantic\/St_Helena","Atlantic\/Stanley","Australia\/Adelaide","Australia\/Brisbane","Australia\/Broken_Hill","Australia\/Darwin","Australia\/Eucla","Australia\/Hobart","Australia\/Lindeman","Australia\/Lord_Howe","Australia\/Melbourne","Australia\/Perth","Australia\/Sydney","Europe\/Amsterdam","Europe\/Andorra","Europe\/Astrakhan","Europe\/Athens","Europe\/Belgrade","Europe\/Berlin","Europe\/Bratislava","Europe\/Brussels","Europe\/Bucharest","Europe\/Budapest","Europe\/Busingen","Europe\/Chisinau","Europe\/Copenhagen","Europe\/Dublin","Europe\/Gibraltar","Europe\/Guernsey","Europe\/Helsinki","Europe\/Isle_of_Man","Europe\/Istanbul","Europe\/Jersey","Europe\/Kaliningrad","Europe\/Kirov","Europe\/Kyiv","Europe\/Lisbon","Europe\/Ljubljana","Europe\/London","Europe\/Luxembourg","Europe\/Madrid","Europe\/Malta","Europe\/Mariehamn","Europe\/Minsk","Europe\/Monaco","Europe\/Moscow","Europe\/Oslo","Europe\/Paris","Europe\/Podgorica","Europe\/Prague","Europe\/Riga","Europe\/Rome","Europe\/Samara","Europe\/San_Marino","Europe\/Sarajevo","Europe\/Saratov","Europe\/Simferopol","Europe\/Skopje","Europe\/Sofia","Europe\/Stockholm","Europe\/Tallinn","Europe\/Tirane","Europe\/Ulyanovsk","Europe\/Vaduz","Europe\/Vatican","Europe\/Vienna","Europe\/Vilnius","Europe\/Volgograd","Europe\/Warsaw","Europe\/Zagreb","Europe\/Zurich","Indian\/Antananarivo","Indian\/Chagos","Indian\/Christmas","Indian\/Cocos","Indian\/Comoro","Indian\/Kerguelen","Indian\/Mahe","Indian\/Maldives","Indian\/Mauritius","Indian\/Mayotte","Indian\/Reunion","Pacific\/Apia","Pacific\/Auckland","Pacific\/Bougainville","Pacific\/Chatham","Pacific\/Chuuk","Pacific\/Easter","Pacific\/Efate","Pacific\/Fakaofo","Pacific\/Fiji","Pacific\/Funafuti","Pacific\/Galapagos","Pacific\/Gambier","Pacific\/Guadalcanal","Pacific\/Guam","Pacific\/Honolulu","Pacific\/Kanton","Pacific\/Kiritimati","Pacific\/Kosrae","Pacific\/Kwajalein","Pacific\/Majuro","Pacific\/Marquesas","Pacific\/Midway","Pacific\/Nauru","Pacific\/Niue","Pacific\/Norfolk","Pacific\/Noumea","Pacific\/Pago_Pago","Pacific\/Palau","Pacific\/Pitcairn","Pacific\/Pohnpei","Pacific\/Port_Moresby","Pacific\/Rarotonga","Pacific\/Saipan","Pacific\/Tahiti","Pacific\/Tarawa","Pacific\/Tongatapu","Pacific\/Wake","Pacific\/Wallis","UTC"],"exchanges":[{"name":"Huobi","slug":"huobi"}],"exchangeCurrencies":[{"exchange":"Huobi","slug":"huobi","currencies":{"BTC.Bitcoin":["USDT.Tron"]}}],"exchangesKeyTypes":[{"exchange":"Huobi","keys":["accessKey","secretKey"]}],"api":{"documentationUrl":"http:\/\/api.merchant.local\/api\/documentation"},"roles":["root","admin","support"],"chain":["trc20usdt","usdterc20","btc","eth"],"registrationEnable":true,"version":"1.1-20231208","processingVersion":{"release":"09.11.10.1","commitHash":"bb821323"},"versions":{"backend":{"tag":"1.0.0","commitHash":"bb821323"},"processing":{"tag":"1.0.0","commitHash":"bb821323"}}},"errors":[]}',
            )),
        ],

    )]
    public function getStatusForDashboard(Request $request)
    {
        $statuses = $this->heartbeatService->getStatusForDashboard();

        return new DefaultResponseResource($statuses);
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     */
    #[OA\Get(
        path: '/stores/heartbeat/financial-stats',
        summary: 'Get dictionaries',
        security: [["bearerAuth" => []]],
        tags: ['Heartbeat'],
        responses: [
            new OA\Response(response: 200, description: "Get static address", content: new OA\JsonContent(
                example: '{"result":{"coldWalletsUsdSum":379430.5311246113,"exchangeWalletsUsdSum":70.26993798999969,"unconfirmedBtcTransactions":0,"lastSuccessfulDepositTransactionTime":"48 minutes,12 seconds ago","lastSuccessfulWithdrawTransactionTime":"48 minutes,12 seconds ago"},"errors":[]}',
            )),
        ],

    )]
    public function getFinancialStatsForDashboard(Authenticatable $user)
    {
        $financialStats = $this->heartbeatService->getUserFinancialStatsForDashboard($user);

        return DefaultResponseResource::make($financialStats);
    }

    /**
     * @param Request $request
     * @return ServiceLogHistoryCollection
     */
    public function getServiceLogHistory(Request $request): ServiceLogHistoryCollection
    {
        $input = $request->input();

        $dto = new ServiceLogHistoryDto([
            'page'          => $input['page'] ?? 1,
            'perPage'       => $input['perPage'] ?? 10,
            'sortField'     => $input['sortField'] ?? 'created_at',
            'sortDirection' => $input['sortDirection'] ?? 'desc',
            'serviceId'     => $input['serviceId'],
            'status'        => !empty($input['status']) ? $input['status'] : null,
        ]);

        $history = $this->heartbeatService->getServiceLogHistory($dto);

        return new ServiceLogHistoryCollection($history);
    }

    /**
     * @return DefaultResponseResource
     */
    public function getResources()
    {
        return DefaultResponseResource::make([
            'queues' => $this->heartbeatService->getQueues(),
            'disk'   => $this->heartbeatService->getDiskSpace(),
        ]);
    }

    /**
     * @return DefaultResponseResource
     */
    public function getAllService()
    {
        return HeartbeatServiceCollection::make($this->heartbeatService->getAllService());
    }

    public function getServiceLaunch(Service $service)
    {
        $launch = ServiceLogLaunch::where('service_id', $service->id)
            ->with(['serviceLogs', 'service'])
            ->orderBy('start_at', 'DESC')
            ->paginate();

        return ServiceLogLaunchCollection::make($launch);
    }
}
