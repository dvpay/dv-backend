<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TranslateWithDeeplCommand extends Command
{
    protected $signature = 'translate:with-deepl {apiKey} {--locale-path=} {--T|target-locales=*} {--S|source-locale=} {--F|force}';

    protected $description = 'Translates Resources with deepl.com service';

    private const DEEPL_LOCALE = 'deepl';
    private string $localePath;
    private string $sourceLocale;
    private array $targetLocales = [
        'AR', 'BG', 'CS', 'DA', 'DE', 'EL', 'EN', 'ES', 'ET', 'FI', 'FR',
        'HU', 'ID', 'IT', 'JA', 'KO', 'LT', 'LV', 'NB', 'NL', 'PL',
        'PT', 'RO', 'RU', 'SK', 'SL', 'SV', 'TR', 'UK', 'ZH',
    ];
    private string $deeplApiKey;
    private bool $skipExisted = true;
    private bool $customPathMode = false;
    private string $deeplApiUrl = 'https://api.deepl.com/v2'; // 'https://api-free.deepl.com/v2' or 'https://api.deepl.com/v2';

    /**
     * Usage:
     * php artisan translate:with-deepl {key here}
     *  php artisan translate:with-deepl {key here} --locale-path=locales/ -Sen -TRU -Tfr
     * @return void
     */

    public function handle(): void
    {

        $this->deeplApiKey = $this->argument('apiKey');

        if(!empty($this->option('target-locales'))) {

            $this->targetLocales = array_map(
                fn($locale): string => Str::upper($locale),
                $this->option('target-locales')
            );
        }

        $this->sourceLocale = Str::upper(config('app.locale'));

        if(!empty($this->option('source-locale'))) {
            $this->sourceLocale = Str::upper($this->option('source-locale'));
        }

        $this->localePath = lang_path() . '/';

        if(!empty($this->option('locale-path'))) {
            $this->localePath = base_path() . '/' . $this->option('locale-path');
            $this->customPathMode = true;
        }

        if($this->option('force')) {
            $this->skipExisted = false;
        }

        $sourceLocaleJson = $this->getJsonFromLocaleFile($this->sourceLocale);
        $deeplLocaleJson = $this->getJsonFromLocaleFile(self::DEEPL_LOCALE);

        $deeplLocaleJson = array_merge($sourceLocaleJson, $deeplLocaleJson);

        $this->putJsonLocaleFileFromArray($deeplLocaleJson,self::DEEPL_LOCALE);

        foreach ($deeplLocaleJson as $deeplLocaleJsonKey => $deeplLocaleJsonItem) {

            if($deeplLocaleJsonItem !== md5($deeplLocaleJsonKey)) {

                $contentToTranslate = $sourceLocaleJson[$deeplLocaleJsonKey] ?? $deeplLocaleJsonKey;

                if(Str::contains($contentToTranslate,'|')) {
                    $this->warn("Translation `{$deeplLocaleJsonKey}`: !!!Skipped!!! Used unsupported symbols ('|'). You should awoid to usr `trans_choice` or other pluralization methods" );
                    continue;
                }

                try {
                    $this->translateToTargetLocalesAndWriteToJson($deeplLocaleJsonKey, $contentToTranslate);
                    $this->info("Translation `{$deeplLocaleJsonKey}`: OK");
                } catch (\Exception $e) {
                    $this->error("Translation `{$deeplLocaleJsonKey}`: !!!ERROR!!! {$e->getMessage()}");
                }
            }
        }
    }

    private function translateToTargetLocalesAndWriteToJson(string $localeKey, string $contentToTranslate)
    {

        foreach ($this->targetLocales as $locale) {

            if( Str::lower($locale) === Str::lower($this->sourceLocale)) {
                continue;
            }


            $targetLocaleJson = $this->getJsonFromLocaleFile($locale);

            if($this->skipExisted && array_key_exists($localeKey, $targetLocaleJson) && $targetLocaleJson[$localeKey] !== $localeKey) {
                continue;
            }

            $targetLocaleJson[$localeKey] = $this->translate($contentToTranslate, $locale);
            $this->putJsonLocaleFileFromArray($targetLocaleJson, $locale);

        }

        $deeplLocaleJson = $this->getJsonFromLocaleFile(self::DEEPL_LOCALE);
        $deeplLocaleJson[$localeKey] = md5($localeKey);
        $this->putJsonLocaleFileFromArray($deeplLocaleJson, self::DEEPL_LOCALE);
    }

    private function translate(string $content, string $targetLocale): string
    {

        $placeholders = $this->getPlaceholders($content);
        $content = $this->collapsePlaceholders($content, $placeholders);

        $data = [
            'text' => [$content],
            'target_lang' => $targetLocale,
            'source_lang' => $this->sourceLocale,
            'preserve_formatting' => true,
            'outline_detection' => true,
            'tag_handling' => 'html',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'DeepL-Auth-Key ' . $this->deeplApiKey,
        ])
            ->asJson()
            ->send('POST', $this->deeplApiUrl . '/translate', [
                'json' => $data,
            ]);

        $translated = $response->json('translations.0.text');

        if(empty ($translated)) {
            throw new \Exception($response->body());
        }

        return $this->restorePlaceholders($translated, $placeholders);

    }

    private function getJsonFromLocaleFile(string $locale): array
    {
        $locale = Str::lower($locale);

        $localeJson = [];

        if(File::exists($this->localePath . $locale . '.json')) {
            $localeJson = File::json($this->localePath . $locale . '.json');
        }

        return $localeJson;
    }

    private function putJsonLocaleFileFromArray(array $translations, string $locale)
    {
        $styledString = str_replace(
            ['","','":"','{"','"}'],
            ["\",".PHP_EOL."  \"","\": \"","{".PHP_EOL."  \"","\"".PHP_EOL."}"],
            json_encode($translations,JSON_UNESCAPED_UNICODE)
        );

        $localePath = $this->localePath .  Str::lower($locale). '.json';
        File::put($localePath, $styledString);
    }

    private function getPlaceholders(string $str): ?array
    {
        $pattern = '/\s*(:\S+)/';
        if (preg_match_all($pattern, $str, $m, PREG_PATTERN_ORDER)) {
            foreach ($m[0] as $k => $v) {
                $m[0][$k] = trim($v);
            }
            return $m[0];
        }

        return null;
    }

    private function collapsePlaceholders(string $str, ?array $placeholders): string
    {
        if ($placeholders) {
            $tr = array_combine(
                $placeholders,
                array_fill(0, count($placeholders), '[[]]'),
            );
            $str = strtr($str, $tr);
        }

        return $str;
    }

    private function restorePlaceholders(string $str, ?array $placeholders): string
    {
        if ($placeholders) {
            $str = preg_replace_array('/\[\[]]/', $placeholders, $str);
        }

        return $str;
    }

}
