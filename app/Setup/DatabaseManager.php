<?php
declare(strict_types=1);

namespace App\Setup;

use App\Exceptions\ApiException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

/**
 *
 */
class DatabaseManager
{
    /**
     * @param Request $request
     * @return true
     * @throws ValidationException
     */
    public function setDBConfig(Request $request): true
    {
        config()->set('database.default', $request->input('database_connection'));
        config()->set('database.connections.' . $request->input('database_connection') . '.host', $request->input('database_hostname'));
        config()->set('database.connections.' . $request->input('database_connection') . '.port', $request->input('database_port'));
        config()->set('database.connections.' . $request->input('database_connection') . '.database', $request->input('database_name'));
        config()->set('database.connections.' . $request->input('database_connection') . '.username', $request->input('database_username'));
        config()->set('database.connections.' . $request->input('database_connection') . '.password', $request->input('database_password'));

        DB::purge($request->input('database_connection'));
        DB::reconnect($request->input('database_connection'));

        // Test database connection
        try {
            DB::connection()->getPdo();
        } catch (Exception $e) {
            throw new ApiException('Database Fail credential', 400);
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function setRedisConfig(Request $request): true
    {
        config()->set('database.redis.default.host', $request->input('redis_host'));
        config()->set('database.redis.default.port', $request->input('redis_port'));
        config()->set('database.redis.default.password', $request->input('redis_password'));

        try {
             Redis::connection('default')->ping();
        } catch (\Exception $e) {
            throw new  Exception('Redis Fail credential');
        }
        return true;
    }
}
