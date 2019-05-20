<?php

namespace Mini\Pagination;

use Mini\Support\ServiceProvider;


class PaginationServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Paginator::viewFactoryResolver(function ()
        {
            return $this->app['view'];
        });

        Paginator::currentPathResolver(function ()
        {
            return $this->app['request']->url();
        });

        Paginator::currentPageResolver(function ($pageName = 'page')
        {
            $page = $this->app['request']->input($pageName);

            if ((filter_var($page, FILTER_VALIDATE_INT) !== false) && ((int) $page >= 1)) {
                return $page;
            }

            return 1;
        });
    }
}
