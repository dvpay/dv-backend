<?php
declare(strict_types=1);

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\SetupRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use App\Setup\DatabaseManager;
use App\Setup\EnvironmentManager;
use App\Setup\Helper\PermissionsHelper;
use App\Setup\Helper\Requirements;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class EnvironmentController extends Controller
{
    public function __construct(
        protected Requirements       $requirement,
        protected PermissionsHelper  $permission,
        protected DatabaseManager    $databaseManager,
        protected EnvironmentManager $environment,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function index()
    {
        if (config('installer.finish')) {
            redirect('/');
        }

        if ($this->requirement->isSupported() && $this->permission->isSupported()) {
            return view('setup.database');
        }

        throw new Exception('Required permission and server requirements is not fulfilled');
    }

    /**
     * @throws Exception
     */
    public function saveEnvironment(SetupRequest $request)
    {
        ini_set('max_execution_time', 0);
        if (config('installer.finish')) {
            throw new Exception('Installation Disabled');
        }

        $this->databaseManager->setDBConfig($request);
        $this->databaseManager->setRedisConfig($request);
        $this->testConnectProcessing($request->input('processing_host'));
        $this->environment->saveFileWizard($request);

        Artisan::call('migrate', ['--force' => true, '--seed' => true]);
        Artisan::call('cache:currency:rate');
        Artisan::call('processing:init');
    }

    /**
     * @throws Exception
     */
    public function admin()
    {
        if (config('installer.finish')) {
            return redirect('/');
        }

        return view('setup.admin');
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function registerUser(RegisterRequest $request)
    {
        ini_set('max_execution_time', 0);

        if (config('installer.finish')) {
            throw new Exception('Installation Disabled', 400);
        }
        $user = User::where('id', 1)->first();

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->saveOrFail();
        //:Artisan::call('register:processing:owner');

        //$this->putPermanentEnv('FINISH_INSTALL', 'true');
        //$this->environment->saveEnvFrontend(config('app.url'));
        $this->nodeInstall();

        return response()->json(
          [
              'app_url' => config('app.app_domain')
          ]
        );

    }

    /**
     * @param string $url
     * @return void
     * @throws Exception
     */
    private function testConnectProcessing(string $url)
    {
        try {
            Http::get($url . '/status')->object();
        } catch (Exception $exception) {
            throw new Exception('Can\'t connection to processing server');
        }
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    private function putPermanentEnv($key, $value)
    {
        $path = app()->environmentFilePath();

        $escaped = preg_quote('=' . env($key), '/');

        file_put_contents($path, preg_replace(
            "/^{$key}{$escaped}/m",
            "{$key}={$value}",
            file_get_contents($path)
        ));
    }

    /**
     * @return void
     */
    private function nodeInstall()
    {
        set_time_limit(600);
        $value = exec('cd ../frontend/ && npm i && npm run build 2>&1');

    }
}
