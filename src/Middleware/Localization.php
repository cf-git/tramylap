<?php

namespace CFGit\Tramylap\Middleware;

use Closure;
use Carbon\Carbon;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->route()->parameter('lang');
        $request->route()->forgetParameter("lang");
        $locale = in_array($locale, config("tramylap.available")) ? $locale : config(
            'tramylap.default',
            config(
                'tramylap.default',
                config('app.locale', 'en')
            )
        );
        app('url')->defaults(['lang' => $locale]);
        app()->setLocale($locale);
        view()->share([
            'locale' => $locale,
            'lang' => $locale
        ]);
        setlocale(LC_TIME, $this->longLocaleAlias($locale));
        \Illuminate\Support\Carbon::setLocale($this->shortLocaleAlias($locale));
        return $next($request);
    }

    protected function longLocaleAlias($locale)
    {
        $alias = [
            'ua' => 'uk_UA.UTF-8',
            'ru' => 'ru_RU.UTF-8',
        ];
        return $alias[$locale] ?? $locale;
    }

    protected function shortLocaleAlias($locale)
    {
        $alias = [
            'ua' => 'uk_UA'
        ];
        return $alias[$locale] ?? $locale;
    }
}
