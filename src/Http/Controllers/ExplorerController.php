<?php

namespace Mevlutcelik\LaravelExplorer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
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

            // 1. URL Path Parametrelerini Yakala
            preg_match_all('/\{([^}]+)\}/', $route->uri(), $matches);
            foreach ($matches[1] as $match) {
                $isOptional = str_ends_with($match, '?');
                $name = str_replace('?', '', $match);
                $params[] = ['name' => $name, 'type' => 'Path', 'required' => !$isOptional];
            }

            // 2. Controller, Closure ve FormRequest Analizi
            try {
                $action = $route->getAction();
                $reflection = null;
                $foundKeys = [];

                if (isset($action['uses']) && is_string($action['uses']) && str_contains($action['uses'], '@')) {
                    list($class, $method) = explode('@', $action['uses']);
                    if (class_exists($class) && method_exists($class, $method)) {
                        $reflection = new ReflectionMethod($class, $method);
                    }
                } elseif (isset($action['uses']) && $action['uses'] instanceof \Closure) {
                    $reflection = new ReflectionFunction($action['uses']);
                }

                if ($reflection) {
                    
                    // --- A. FORM REQUEST DOSYALARINI OKU (Örn: LoginRequest) ---
                    foreach ($reflection->getParameters() as $param) {
                        $type = $param->getType();
                        if ($type && !$type->isBuiltin()) {
                            $typeName = $type->getName();
                            if (is_subclass_of($typeName, '\Illuminate\Foundation\Http\FormRequest')) {
                                try {
                                    $reqRef = new ReflectionClass($typeName);
                                    if ($reqRef->hasMethod('rules')) {
                                        $ruleMethod = $reqRef->getMethod('rules');
                                        $rFile = $ruleMethod->getFileName();
                                        $rStart = $ruleMethod->getStartLine() - 1;
                                        $rLen = $ruleMethod->getEndLine() - $rStart;
                                        
                                        if ($rFile && is_readable($rFile)) {
                                            $rSource = file($rFile);
                                            $rBody = implode("", array_slice($rSource, $rStart, $rLen));
                                            
                                            preg_match_all('/[\'"]([a-zA-Z0-9_\.]+)[\'"]\s*=>/', $rBody, $rMatches);
                                            if (!empty($rMatches[1])) {
                                                $foundKeys = array_merge($foundKeys, $rMatches[1]);
                                            }
                                        }
                                    }
                                } catch (Throwable $e) {}
                            }
                        }
                    }

                    // --- B. METODUN KENDİ İÇİNİ OKU ---
                    $file = $reflection->getFileName();
                    $startLine = $reflection->getStartLine() - 1;
                    $endLine = $reflection->getEndLine();
                    $length = $endLine - $startLine;

                    if ($file && is_readable($file)) {
                        $source = file($file);
                        $methodBody = implode("", array_slice($source, $startLine, $length));

                        // 1. Normal kullanımlar: ->input('email'), request('email')
                        preg_match_all('/(?:->input|->get|request\()\s*\(\s*[\'"]([a-zA-Z0-9_]+)[\'"]/', $methodBody, $m1);
                        
                        // 2. Sihirli özellikler: $request->email (method çağrılarını hariç tut)
                        preg_match_all('/\$request->([a-zA-Z0-9_]+)(?!\()/', $methodBody, $m2);
                        
                        // 3. Doğrudan validate dizileri: 'email' => 'required'
                        preg_match_all('/[\'"]([a-zA-Z0-9_\.]+)[\'"]\s*=>\s*[\'"]/', $methodBody, $m3);
                        
                        // 4. ->only('email', 'password') VEYA ->only(['email', 'password'])
                        // Hem köşeli parantezli hem parantezsiz kullanımları yakalar
                        preg_match_all('/->(?:only|except)\(\s*(.*?)\s*\)/s', $methodBody, $m4Raw);
                        $m4 = [];
                        if (!empty($m4Raw[1])) {
                            foreach ($m4Raw[1] as $onlyBlock) {
                                // Bloğun içindeki tüm string (tırnak içi) değerleri al
                                preg_match_all('/[\'"]([a-zA-Z0-9_]+)[\'"]/', $onlyBlock, $onlyKeys);
                                $m4 = array_merge($m4, $onlyKeys[1] ?? []);
                            }
                        }

                        $foundKeys = array_merge($foundKeys, $m1[1] ?? [], $m2[1] ?? [], $m3[1] ?? [], $m4);
                    }

                    // Bulunan parametreleri temizle ve filtrele (Sistem değişkenlerini gizle)
                    $ignoreList = [
                        'user', 'ip', 'url', 'fullUrl', 'method', 'path', 'ajax', 'pjax', 
                        'secure', 'bearerToken', 'cookie', 'header', 'server', 'session',
                        'all', 'input', 'query', 'boolean', 'date', 'enum', 'string', 'integer',
                        'validate', 'validated', 'safe', 'only', 'except', 'has', 'hasAny',
                        'filled', 'anyFilled', 'missing', 'whenHas', 'whenFilled', 'whenMissing',
                        'merge', 'mergeIfMissing', 'replace', 'json', 'content', 'route', 'onl'
                    ];

                    $foundKeys = array_unique(array_filter($foundKeys, function($k) use ($ignoreList) {
                        return !in_array($k, $ignoreList) && !empty($k);
                    }));

                    foreach ($foundKeys as $key) {
                        if (!in_array($key, array_column($params, 'name'))) {
                            $params[] = ['name' => $key, 'type' => 'Body/Query', 'required' => false];
                        }
                    }
                }
            } catch (Throwable $e) {}

            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => empty($middleware) ? ['-'] : $middleware,
                'params' => $params
            ];
        })->filter(function ($route) {
            $ignoreUris = [
                config('laravel-explorer.path', 'explorer'),
                '_debugbar/{routes}',
                '_boost/browser-logs',
                'storage/{path}',
                'up',
                'sanctum/csrf-cookie',
            ];

            if (in_array($route['uri'], $ignoreUris)) {
                return false;
            }

            if (str_starts_with($route['uri'], '_')) {
                return false;
            }

            return true;
        })->sortBy('uri')->values()->all();

        return view('laravel-explorer::index', ['routes' => $routes]);
    }
}