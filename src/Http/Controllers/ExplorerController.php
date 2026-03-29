<?php

namespace Mevlutcelik\LaravelExplorer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

class ExplorerController extends Controller
{
    public function index()
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            $middleware = $route->gatherMiddleware();
            $params = [];

            // 1. URL Path Parametrelerini Yakala (Örn: /api/users/{id})
            preg_match_all('/\{([^}]+)\}/', $route->uri(), $matches);
            foreach ($matches[1] as $match) {
                $isOptional = str_ends_with($match, '?');
                $name = str_replace('?', '', $match);
                $params[] = ['name' => $name, 'type' => 'Path', 'required' => !$isOptional];
            }

            // 2. Closure veya Controller içindeki Request parametrelerini analiz et
            try {
                $action = $route->getAction();
                $reflection = null;

                if (isset($action['uses']) && is_string($action['uses']) && str_contains($action['uses'], '@')) {
                    list($class, $method) = explode('@', $action['uses']);
                    if (class_exists($class) && method_exists($class, $method)) {
                        $reflection = new \ReflectionMethod($class, $method);
                    }
                } elseif (isset($action['uses']) && $action['uses'] instanceof \Closure) {
                    $reflection = new \ReflectionFunction($action['uses']);
                }

                if ($reflection) {
                    $file = $reflection->getFileName();
                    $startLine = $reflection->getStartLine() - 1;
                    $endLine = $reflection->getEndLine();
                    $length = $endLine - $startLine;

                    if ($file && is_readable($file)) {
                        $source = file($file);
                        $methodBody = implode("", array_slice($source, $startLine, $length));

                        preg_match_all('/(?:->input|->get|request\()\s*\(\s*[\'"]([a-zA-Z0-9_]+)[\'"]/', $methodBody, $bodyMatches);
                        
                        $foundKeys = array_unique($bodyMatches[1] ?? []);

                        foreach ($foundKeys as $key) {
                            if (!in_array($key, array_column($params, 'name'))) {
                                $params[] = ['name' => $key, 'type' => 'Body/Query', 'required' => false];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Sessizce geç
            }

            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => empty($middleware) ? ['-'] : $middleware,
                'params' => $params
            ];
        })->filter(function ($route) {
            // Gizlenmesini istediğimiz rotalar
            $ignoreUris = [
                config('laravel-explorer.path', 'explorer'), // Kendi arayüzümüzü gizle
                '_debugbar/{routes}',
                '_boost/browser-logs',
                'storage/{path}',
                'up',
                'sanctum/csrf-cookie',
            ];

            // Birebir eşleşenleri filtrele
            if (in_array($route['uri'], $ignoreUris)) {
                return false;
            }

            // Alt çizgi ile başlayan sistem/paket rotalarını filtrele (_ignition vb.)
            if (str_starts_with($route['uri'], '_')) {
                return false;
            }

            return true;
        })->sortBy('uri')->values()->all();

        // Paketin view dosyasını çağır
        return view('laravel-explorer::index', ['routes' => $routes]);
    }
}