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
                $extractedParams = [];

                if (isset($action['uses']) && is_string($action['uses']) && str_contains($action['uses'], '@')) {
                    list($class, $method) = explode('@', $action['uses']);
                    if (class_exists($class) && method_exists($class, $method)) {
                        $reflection = new ReflectionMethod($class, $method);
                    }
                } elseif (isset($action['uses']) && $action['uses'] instanceof \Closure) {
                    $reflection = new ReflectionFunction($action['uses']);
                }

                if ($reflection) {
                    
                    // --- A. FORM REQUEST DOSYALARINI OKU ---
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
                                            
                                            preg_match_all('/[\'"]([a-zA-Z0-9_\.\-]+)[\'"]\s*=>\s*([^,\]]+)/', $rBody, $rMatches);
                                            foreach ($rMatches[1] as $idx => $key) {
                                                $extractedParams[$key] = str_contains($rMatches[2][$idx], 'required');
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

                        // 1. validate() blokları
                        if (preg_match_all('/validate\s*\(\s*\[(.*?)\]\s*\)/s', $methodBody, $valBlocks)) {
                            foreach ($valBlocks[1] as $block) {
                                preg_match_all('/[\'"]([a-zA-Z0-9_\.\-]+)[\'"]\s*=>\s*([^,\]]+)/', $block, $valMatches);
                                foreach ($valMatches[1] as $idx => $k) {
                                    $extractedParams[$k] = str_contains($valMatches[2][$idx], 'required');
                                }
                            }
                        }

                        // 2. Array alabilen metodlar: ->only, ->except, ->has, ->filled vb.
                        if (preg_match_all('/->(?:only|except|has|hasAny|filled|anyFilled|missing)\(\s*(.*?)\s*\)/s', $methodBody, $arrayBlocks)) {
                            foreach ($arrayBlocks[0] as $idx => $fullMatch) {
                                $isRequired = str_starts_with(trim($fullMatch), '->only');
                                preg_match_all('/[\'"]([a-zA-Z0-9_\.\-]+)[\'"]/', $arrayBlocks[1][$idx], $keys);
                                foreach (($keys[1] ?? []) as $k) {
                                    if (!isset($extractedParams[$k])) {
                                        $extractedParams[$k] = $isRequired;
                                    }
                                }
                            }
                        }

                        // 3. Tekli getter'lar: ->input('email'), request('email') vb.
                        preg_match_all('/(?:->input|->get|->query|->post|->boolean|->date|->string|->integer|->enum|request\()\s*\(\s*[\'"]([a-zA-Z0-9_\.\-]+)[\'"]/', $methodBody, $m1);
                        foreach (($m1[1] ?? []) as $k) {
                            if (!isset($extractedParams[$k])) $extractedParams[$k] = false;
                        }
                        
                        // 4. Sihirli özellikler: $request->email (\b ile kelime kırpılmaları engellendi)
                        preg_match_all('/\$request->([a-zA-Z0-9_]+)\b(?!\s*\()/', $methodBody, $m2);
                        foreach (($m2[1] ?? []) as $k) {
                            if (!isset($extractedParams[$k])) $extractedParams[$k] = false;
                        }

                        // 5. Array kullanımı: $request['email']
                        preg_match_all('/\$request\s*\[\s*[\'"]([a-zA-Z0-9_\.\-]+)[\'"]\s*\]/', $methodBody, $m3);
                        foreach (($m3[1] ?? []) as $k) {
                            if (!isset($extractedParams[$k])) $extractedParams[$k] = false;
                        }
                    }

                    // Bulunan parametreleri temizle ve filtrele (Sistem değişkenlerini gizle)
                    $ignoreList = [
                        'user', 'ip', 'url', 'fullUrl', 'method', 'path', 'ajax', 'pjax', 
                        'secure', 'bearerToken', 'cookie', 'header', 'server', 'session',
                        'all', 'input', 'query', 'boolean', 'date', 'enum', 'string', 'integer',
                        'validate', 'validated', 'safe', 'only', 'except', 'has', 'hasAny',
                        'filled', 'anyFilled', 'missing', 'whenHas', 'whenFilled', 'whenMissing',
                        'merge', 'mergeIfMissing', 'replace', 'json', 'content', 'route'
                    ];

                    foreach ($extractedParams as $key => $isRequired) {
                        if (in_array($key, $ignoreList) || empty($key)) {
                            continue;
                        }
                        if (!in_array($key, array_column($params, 'name'))) {
                            $params[] = ['name' => $key, 'type' => 'Body/Query', 'required' => $isRequired];
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