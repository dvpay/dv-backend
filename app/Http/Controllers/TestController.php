<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function webhook(Request $request): mixed {
        \Log::error("webhook", [
            "request" => $request,
            "headers" => $request->headers->all(),
        ]);

        return response()->json("")->setStatusCode(200);
    }
}