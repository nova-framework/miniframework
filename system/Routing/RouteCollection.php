<?php

namespace Mini\Routing;

use Mini\Http\Request;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Countable;


class RouteCollection implements Countable
{
    /**
     * An array of the routes keyed by method.
     *
     * @var array
     */
    protected $routes = array(
        'GET'     => array(),
        'POST'    => array(),
        'PUT'     => array(),
        'DELETE'  => array(),
        'PATCH'   => array(),
        'HEAD'    => array(),
        'OPTIONS' => array(),
    );

    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = array();

    /**
     * A look-up table of routes by their names.
     *
     * @var array
     */
    protected $namedRoutes = array();


    /**
     * Add a Route instance to the collection.
     *
     * @param  \Mini\Routing\Route  $route
     * @return \Mini\Routing\Route
     */
    public function add(Route $route)
    {
        $path = $route->getPath();

        foreach ($route->getMethods() as $method) {
            $this->routes[$method][$path] = $route;
        }

        $this->allRoutes[] = $route;

        if (! empty($name = $route->getName())) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    /**
     * Find the Route which matches the given Request.
     *
     * @param  \Mini\Http\Request  $request
     * @return \Mini\Routing|Route|null
     */
    public function match(Request $request)
    {
        $routes = $this->get($request->method());

        if (! is_null($route = $this->findRoute($routes, $request))) {
            return $route;
        }

        throw new NotFoundHttpException();
    }

    /**
     * Determine if a route in the array matches the request.
     *
     * @param  array  $routes
     * @param  \Mini\Http\Request  $request
     * @return \Mini\Routing\Route|null
     */
    protected function findRoute(array $routes, Request $request)
    {
        $path = '/' .trim($request->path(), '/');

        if (! is_null($route = array_get($routes, $path = rawurldecode($path)))) {
            return $route->matched();
        }

        $orderedRoutes = $this->prepareRoutes($routes);

        return array_first($orderedRoutes, function ($uri, $route) use ($path)
        {
            return $route->matches($path);
        });
    }

    /**
     * Moves fallback routes to the end of given routes list.
     *
     * @param  array  $routes
     * @return array
     */
    protected function prepareRoutes(array $routes)
    {
        $items = $fallbacks = array();

        foreach ($routes as $key => $route) {
            if ($route->isFallback()) {
                $fallbacks[$key] = $route;
            } else {
                $items[$key] = $route;
            }
        }

        return array_merge($items, $fallbacks);
    }

    /**
     * Determine if the route collection contains a given named route.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasNamedRoute($name)
    {
        return array_has($this->namedRoutes, $name);
    }

    /**
     * Get a route instance by its name.
     *
     * @param  string  $name
     * @return \Nova\Routing\Route|null
     */
    public function getByName($name)
    {
        return array_get($this->namedRoutes, $name);
    }

    /**
     * Get all of the routes in the collection.
     *
     * @param  string|null  $method
     * @return array
     */
    protected function get($method = null)
    {
        if (is_null($method)) {
            return $this->getRoutes();
        }

        return array_get($this->routes, $method, array());
    }

    /**
     * Get all registered routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->allRoutes;
    }

    /**
     * Get the registered named routes.
     *
     * @return array
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->allRoutes);
    }
}
