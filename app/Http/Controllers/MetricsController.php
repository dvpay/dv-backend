<?php

namespace App\Http\Controllers;

use App\Enums\Metric;
use App\Facades\Prometheus;
use App\Services\Prometheus\PrometheusExporterInterface;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    public function prometheus(PrometheusExporterInterface $exporter) {

        #TODO: For tests
//        Prometheus::counterInc(Metric::ProcessingCallbackReceived->getName(),5,['sdf']);

        $formatter = new RenderTextFormat();

        $metrics = $exporter->export();
        return response(
            $formatter->render($metrics),
            200,
            [
                'Content-Type' => RenderTextFormat::MIME_TYPE,
            ]
        );
    }
}