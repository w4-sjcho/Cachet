<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\Finder\Finder;

class TranslationConverterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cachet:translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert translations into their JSON format.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $localizations = [];

        $defaultLocalizations = $this->getDefaultTranslations();

        foreach ((new Finder())->in(resource_path().'/lang')->directories() as $localizations) {
            $locale = $localizations->getRelativePathname();

            if ($locale === 'en') {
                $localizations = $defaultLocalizations;
            } else {
                foreach ((new Finder())->in($localizations->getPathname())->files() as $file) {
                    $strings = Lang::get(basename($file->getFilename(), '.php'), [], $locale);

                    $localizations = collect($strings)->flatten()->flatMap(function ($string) use ($defaultLocalizations) {
                        return [$string => $string];
                    })->all();
                }
            }

            file_put_contents(
                resource_path()."/lang/{$locale}.json",
                json_encode($localizations)
            );
        }
    }

    protected function getDefaultTranslations()
    {
        $defaultLocale = 'en';
        $localizations = [];

        foreach ((new Finder())->in(resource_path().'/lang/'.$defaultLocale)->files() as $file) {
            $strings = Lang::get(basename($file->getFilename(), '.php'), [], $defaultLocale);

            $localizations = collect($strings)->flatten()->dd()->flatMap(function ($string, $key) {
                dd(func_get_args());
                dd($string, $key);

                return [$string => $string];
            })->unique()->all();
        }

        return $localizations;
    }
}
