<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Explorer</title>
    
    <script>
        if (localStorage.getItem('laravel_explorer_theme') === 'dark' || (!('laravel_explorer_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        laravel: '#FF2D20', // Official Laravel Red
                        zinc: { 850: '#18181b', 900: '#18181b', 950: '#0a0a0a' }
                    },
                    animation: { 'fade-in': 'fadeIn 0.2s ease-out' },
                    keyframes: { fadeIn: { from: { opacity: 0 }, to: { opacity: 1 } } }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Figtree:wght@400;500;600;700&display=swap');
        
        body, .font-sans, input, textarea, select, button { font-family: 'Figtree', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Scrollbar */
        * { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
        .dark * { scrollbar-color: #3f3f46 transparent; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #3f3f46; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #52525b; }

        /* Badges */
        .badge { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 99px; border: 1px solid transparent; display: inline-flex; align-items: center; justify-content: center; }
        .badge-get { background: #f0fdf4; color: #059669; border-color: #bbf7d0; }
        .badge-post { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .badge-put { background: #fffbeb; color: #d97706; border-color: #fde68a; }
        .badge-patch { background: #fff7ed; color: #ea580c; border-color: #ffedd5; }
        .badge-delete { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .badge-default { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

        .dark .badge-get { background: rgba(16,185,129,0.1); color: #34d399; border-color: rgba(16,185,129,0.2); }
        .dark .badge-post { background: rgba(59,130,246,0.1); color: #60a5fa; border-color: rgba(59,130,246,0.2); }
        .dark .badge-put { background: rgba(245,158,11,0.1); color: #fbbf24; border-color: rgba(245,158,11,0.2); }
        .dark .badge-patch { background: rgba(249,115,22,0.1); color: #fb923c; border-color: rgba(249,115,22,0.2); }
        .dark .badge-delete { background: rgba(239,68,68,0.1); color: #f87171; border-color: rgba(239,68,68,0.2); }
        .dark .badge-default { background: rgba(100,116,139,0.1); color: #94a3b8; border-color: rgba(100,116,139,0.2); }

        /* Table Rows */
        .route-row { transition: all 0.15s; border-bottom: 1px solid #f1f5f9; }
        .dark .route-row { border-bottom: 1px solid #27272a; }
        .route-row:hover { background: #f8fafc; }
        .dark .route-row:hover { background: #18181b; }
        .route-row.active { background: #fef2f2; box-shadow: inset 3px 0 0 0 #FF2D20; }
        .dark .route-row.active { background: rgba(255,45,32,0.05); box-shadow: inset 3px 0 0 0 #FF2D20; }

        /* Panels */
        .panel-slide { transform: translateX(100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0s 0.3s; visibility: hidden; }
        .panel-slide.open { transform: translateX(0); visibility: visible; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0s 0s; box-shadow: -10px 0 30px rgba(0,0,0,0.05); }
        .dark .panel-slide.open { box-shadow: -10px 0 40px rgba(0,0,0,0.5); }

        /* Inputs */
        .code-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; color: #0f172a; font-family: 'JetBrains Mono', monospace; font-size: 11px; padding: 8px 12px; transition: border-color 0.2s; resize: none; width: 100%;}
        .code-input:focus { outline: none; border-color: #FF2D20; }
        .dark .code-input { background: #0a0a0a; border-color: #27272a; color: #e2e8f0; }
        .dark .code-input:focus { border-color: #FF2D20; }
        
        /* Tabs */
        .tab-btn { color: #64748b; border-bottom: 2px solid transparent; transition: all 0.2s; }
        .tab-btn.active { color: #FF2D20; border-bottom-color: #FF2D20; font-weight: 600; }
        .dark .tab-btn { color: #a1a1aa; }
        .dark .tab-btn.active { color: #FF2D20; border-bottom-color: #FF2D20; }

        .output-tab-btn { color: #64748b; border-bottom: 2px solid transparent; transition: all 0.2s; }
        .output-tab-btn.active { color: #0f172a; border-bottom-color: #FF2D20; font-weight: 600; }
        .dark .output-tab-btn { color: #a1a1aa; }
        .dark .output-tab-btn.active { color: #f4f4f5; border-bottom-color: #FF2D20; }

        .mode-btn { background: transparent; color: #64748b; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .mode-btn.active { background: #fef2f2; color: #FF2D20; border-color: #fca5a5; }
        .dark .mode-btn { color: #a1a1aa; border-color: #27272a; }
        .dark .mode-btn.active { background: rgba(255,45,32,0.1); color: #FF2D20; border-color: rgba(255,45,32,0.3); }
        
        .console-log { border-bottom: 1px solid #f1f5f9; padding: 4px 0; }
        .dark .console-log { border-bottom: 1px solid #27272a; }
    </style>
</head>
<body class="bg-white text-gray-900 dark:bg-zinc-950 dark:text-gray-200 font-sans selection:bg-red-500/20 antialiased overflow-hidden transition-colors duration-200">

<div class="h-screen flex flex-col relative grid-bg">

    <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-cyan-900/20 blur-[120px] pointer-events-none"></div>

    <header class="bg-white dark:bg-zinc-950 border-b border-gray-200 dark:border-zinc-800 h-14 shrink-0 z-50 flex items-center justify-between px-6 w-full">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 flex items-center justify-center text-[#FF2D20]">
                <svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504.4,115.83a5.72,5.72,0,0,0-.28-.68,8.52,8.52,0,0,0-.53-1.25,6,6,0,0,0-.54-.71,9.36,9.36,0,0,0-.72-.94c-.23-.22-.52-.4-.77-.6a8.84,8.84,0,0,0-.9-.68L404.4,55.55a8,8,0,0,0-8,0L300.12,111h0a8.07,8.07,0,0,0-.88.69,7.68,7.68,0,0,0-.78.6,8.23,8.23,0,0,0-.72.93c-.17.24-.39.45-.54.71a9.7,9.7,0,0,0-.52,1.25c-.08.23-.21.44-.28.68a8.08,8.08,0,0,0-.28,2.08V223.18l-80.22,46.19V63.44a7.8,7.8,0,0,0-.28-2.09c-.06-.24-.2-.45-.28-.68a8.35,8.35,0,0,0-.52-1.24c-.14-.26-.37-.47-.54-.72a9.36,9.36,0,0,0-.72-.94,9.46,9.46,0,0,0-.78-.6,9.8,9.8,0,0,0-.88-.68h0L115.61,1.07a8,8,0,0,0-8,0L11.34,56.49h0a6.52,6.52,0,0,0-.88.69,7.81,7.81,0,0,0-.79.6,8.15,8.15,0,0,0-.71.93c-.18.25-.4.46-.55.72a7.88,7.88,0,0,0-.51,1.24,6.46,6.46,0,0,0-.29.67,8.18,8.18,0,0,0-.28,2.1v329.7a8,8,0,0,0,4,6.95l192.5,110.84a8.83,8.83,0,0,0,1.33.54c.21.08.41.2.63.26a7.92,7.92,0,0,0,4.1,0c.2-.05.37-.16.55-.22a8.6,8.6,0,0,0,1.4-.58L404.4,400.09a8,8,0,0,0,4-6.95V287.88l92.24-53.11a8,8,0,0,0,4-7V117.92A8.63,8.63,0,0,0,504.4,115.83ZM111.6,17.28h0l80.19,46.15-80.2,46.18L31.41,63.44Zm88.25,60V278.6l-46.53,26.79-33.69,19.4V123.5l46.53-26.79Zm0,412.78L23.37,388.5V77.32L57.06,96.7l46.52,26.8V338.68a6.94,6.94,0,0,0,.12.9,8,8,0,0,0,.16,1.18h0a5.92,5.92,0,0,0,.38.9,6.38,6.38,0,0,0,.42,1v0a8.54,8.54,0,0,0,.6.78,7.62,7.62,0,0,0,.66.84l0,0c.23.22.52.38.77.58a8.93,8.93,0,0,0,.86.66l0,0,0,0,92.19,52.18Zm8-106.17-80.06-45.32,84.09-48.41,92.26-53.11,80.13,46.13-58.8,33.56Zm184.52,4.57L215.88,490.11V397.8L346.6,323.2l45.77-26.15Zm0-119.13L358.68,250l-46.53-26.79V131.79l33.69,19.4L392.37,178Zm8-105.28-80.2-46.17,80.2-46.16,80.18,46.15Zm8,105.28V178L455,151.19l33.68-19.4v91.39h0Z"/></svg>
            </div>
            <h1 class="flex flex-col text-base font-semibold tracking-tight text-gray-900 dark:text-white">
                <span class="flex">Laravel Explorer</span>
                <span class="flex -mt-1.5 text-[9px] rounded text-gray-600 dark:text-zinc-400">Created by &nbsp;<a href="https://www.mevlutcelik.com" target="_blank" class="text-laravel hover:underline">Mevlüt Çelik</a></span>
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <div id="headerStats" class="hidden lg:flex items-center gap-3 text-[10px] font-mono border-r border-gray-200 dark:border-zinc-800 pr-4 mr-1"></div>
            
            <div class="hidden md:flex items-center gap-2 border-r border-gray-200 dark:border-zinc-800 pr-4 mr-1 group" title="Bearer Token (for protected routes)">
                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500 group-focus-within:text-laravel transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <div class="relative flex items-center">
                    <input type="password" id="headerBearerToken" placeholder="Bearer Token..." class="bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded px-2 py-1 text-[10px] font-mono text-gray-900 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-laravel focus:ring-offset-2 focus:ring-offset-white transition dark:focus:ring-offset-zinc-950 w-64 placeholder:text-gray-400 dark:placeholder:text-zinc-600">
                    <button type="button" onclick="toggleTokenVisibility('headerBearerToken', this)" class="absolute right-1.5 text-gray-400 hover:text-gray-600 dark:text-zinc-500 dark:hover:text-zinc-300 transition-colors" title="Toggle Visibility">
                        <svg class="w-3.5 h-3.5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg class="w-3.5 h-3.5 eye-slash-icon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    </button>
                </div>
            </div>

            <button id="themeToggle" class="text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-colors" title="Toggle Theme">
                <i id="themeIconLight" class="w-4 h-4 hidden dark:block" data-lucide="sun" style="stroke-width: 2.5;"></i>
                <svg id="themeIconDark" class="w-4 h-4 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>

            <button id="exportBtn" class="flex items-center gap-1.5 px-3 py-1 rounded bg-white dark:bg-zinc-900 hover:bg-gray-50 dark:hover:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-[11px] font-medium transition-colors">
                <i class="w-3.5 h-3.5" data-lucide="download" style="stroke-width: 2.5;"></i>
                <span>Export</span>
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden relative z-10 bg-gray-50 dark:bg-zinc-950">

        <div class="w-[60%] flex flex-col relative border-r border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 overflow-hidden">
            
            <div class="flex flex-col gap-3 shrink-0">
                <div class="flex gap-3 pt-4 px-3">
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input id="search" type="search" placeholder="Search routes... (⌘K)" class="w-full pl-9 pr-3 py-2 rounded-full bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-xs text-gray-900 dark:text-zinc-200 placeholder:text-gray-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-laravel focus:ring-offset-2 focus:ring-offset-white transition dark:focus:ring-offset-zinc-950">
                    </div>
                    <select id="middlewareFilter" class="w-40 bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-full px-2 py-2 text-xs text-gray-700 dark:text-zinc-300 focus:outline-none focus:ring-2 focus:ring-laravel focus:ring-offset-2 focus:ring-offset-white transition dark:focus:ring-offset-zinc-950 appearance-none cursor-pointer">
                        <option value="all">All Middleware</option>
                    </select>
                </div>
                <div class="flex items-center justify-between px-3 py-1.5 bg-gray-100 dark:bg-zinc-900 border-b border-t border-gray-200 dark:border-zinc-800 shrink-0">
                    <div id="methodFilters" class="flex flex-wrap gap-1.5"></div>
                    <div id="stats" class="text-[10px] font-mono text-gray-500 dark:text-zinc-500">
                        <span id="statsVisible" class="text-gray-900 dark:text-white font-bold">0</span> / <span id="statsTotal">0</span>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-auto relative">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="sticky top-0 bg-white/95 dark:bg-zinc-950/95 backdrop-blur z-20 border-b border-gray-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-widest cursor-pointer hover:text-gray-900 dark:hover:text-white" onclick="toggleSort('uri')">Endpoint</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-widest">Methods</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-widest hidden xl:table-cell">Action</th>
                            <th class="px-4 py-2.5 text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-widest text-right">Run</th>
                        </tr>
                    </thead>
                    <tbody id="routeTableBody">
                        @foreach($routes as $route)
                        @php
                            $isProtected = collect($route['middleware'])->contains(function($m) {
                                return str_contains(strtolower($m), 'auth');
                            });
                        @endphp
                        <tr data-uri="{{ strtolower($route['uri']) }}" data-name="{{ strtolower($route['name'] ?? '') }}" data-action="{{ strtolower($route['action']) }}" data-methods="{{ strtolower(implode(',', $route['methods'])) }}" data-middleware="{{ strtolower(implode(',', $route['middleware'])) }}" data-params="{{ json_encode($route['params'] ?? []) }}" data-auth="{{ $isProtected ? 'true' : 'false' }}" class="route-row cursor-pointer group" onclick="openDetail(this)">
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-[2px]">
                                    <div class="flex items-center gap-1.5 font-mono text-[13px] text-gray-900 dark:text-gray-200 transition-colors">
                                        @if($isProtected)
                                            <svg class="w-3 h-3 text-gray-400 dark:text-zinc-500 shrink-0" title="Requires Authentication" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        @endif
                                        <span class="truncate">/{{ ltrim($route['uri'], '/') }}</span>
                                    </div>
                                    @if(!empty($route['name']))
                                        <div class="text-[10px] text-gray-500 dark:text-zinc-500 ml{{ $isProtected ? '-4.5' : '' }}">{{ $route['name'] }}</div>
                                    @else
                                        <div class="text-[10px] text-gray-400 dark:text-zinc-600 italic ml{{ $isProtected ? '-4.5' : '' }}">No Name</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1 flex-wrap">
                                    @foreach($route['methods'] as $method)
                                    @php
                                        $cls = match(strtoupper($method)) {
                                            'GET' => 'badge-get', 'POST' => 'badge-post', 'PUT' => 'badge-put', 'PATCH' => 'badge-patch', 'DELETE' => 'badge-delete', default => 'badge-default'
                                        };
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ strtoupper($method) }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden xl:table-cell font-mono text-[11px] text-gray-500 dark:text-zinc-400 truncate max-w-[150px]" title="{{ $route['action'] }}">
                                @if(strtolower($route['action']) === 'closure')
                                    <span class="text-gray-400 dark:text-zinc-500 italic bg-gray-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">closure</span>
                                @else
                                    {{ str_replace(['App\\Http\\Controllers\\', 'App\\Http\\'], '', $route['action']) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="opacity-0 group-hover:opacity-100 px-2 py-1 rounded bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-all text-[9px] font-bold uppercase" onclick="event.stopPropagation(); openDetail(this.closest('tr')); switchTab('test');">
                                    Inspect
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div id="emptyState" class="hidden flex-col items-center justify-center text-center absolute inset-0 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-sm z-30">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white text-sm font-bold mb-1">No routes found</h3>
                    <p class="text-gray-500 dark:text-zinc-400 text-[11px]">Try modifying your search.</p>
                </div>
            </div>

            <div id="inspectorPanel" class="panel-slide absolute top-0 right-0 h-full w-[400px] bg-white dark:bg-zinc-900 z-50 flex flex-col border-l border-gray-200 dark:border-zinc-800 shadow-xl">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-zinc-800 shrink-0">
                    <h2 class="text-xs font-bold text-gray-900 dark:text-white tracking-tight">Inspector</h2>
                    <button onclick="closeDetail()" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-400 dark:text-zinc-500 transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                
                <div class="p-4 border-b border-gray-200 dark:border-zinc-800 shrink-0 bg-gray-50 dark:bg-zinc-950/50">
                    <div class="flex flex-wrap gap-1.5 mb-1.5" id="insMethods"></div>
                    <div class="font-mono text-xs text-gray-900 dark:text-gray-200 break-all" id="insUrl">/select/a/route</div>
                </div>

                <div class="flex px-4 border-b border-gray-200 dark:border-zinc-800 shrink-0 gap-5">
                    <button class="tab-btn active py-2.5 text-[11px] font-medium uppercase tracking-wider" onclick="switchTab('info')">Overview</button>
                    <button class="tab-btn py-2.5 text-[11px] font-medium uppercase tracking-wider" onclick="switchTab('test')">Builder</button>
                    <button class="tab-btn py-2.5 text-[11px] font-medium uppercase tracking-wider" onclick="switchTab('code')">Code</button>
                </div>

                <div class="flex-1 overflow-y-auto relative">
                    <div id="tab-info" class="p-5 space-y-5 block">
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500 mb-1">Name</div>
                            <div id="insName" class="text-xs text-gray-900 dark:text-gray-300 font-mono">-</div>
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500 mb-1">Action</div>
                            <div id="insAction" class="text-xs text-gray-600 dark:text-zinc-400 font-mono break-all">-</div>
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500 mb-1">Middleware</div>
                            <div id="insMiddleware" class="flex flex-wrap gap-1 mt-1.5">-</div>
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500 mb-1.5 flex items-center gap-1">
                                Expected Parameters
                            </div>
                            <div id="insParams" class="space-y-1.5 mt-2"></div>
                        </div>
                    </div>

                    <div id="tab-test" class="p-4 flex flex-col h-full hidden">
                        <div class="mb-3 flex gap-2">
                            <div class="flex-1">
                                <label class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500 mb-1.5 block">Method</label>
                                <select id="reqMethod" class="w-full bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded px-2 py-1.5 text-xs text-gray-900 dark:text-white font-mono outline-none focus:border-laravel">
                                    <option value="GET">GET</option><option value="POST">POST</option><option value="PUT">PUT</option><option value="DELETE">DELETE</option><option value="PATCH">PATCH</option>
                                </select>
                            </div>
                        </div>

                        <div id="inspectorAuthContainer" class="mb-3 hidden">
                            <label class="text-[9px] font-bold uppercase tracking-widest text-amber-600 dark:text-amber-500 mb-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Bearer Token <span class="text-gray-400 dark:text-zinc-500 lowercase tracking-normal font-normal ml-1">(Required)</span>
                            </label>
                            <div class="relative flex items-center">
                                <input type="password" id="inspectorBearerToken" placeholder="Paste your token here..." class="w-full bg-amber-50 dark:bg-amber-500/5 border border-amber-200 dark:border-amber-500/30 rounded pl-2 pr-8 py-1.5 text-xs text-amber-900 dark:text-amber-100 font-mono outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all placeholder:text-amber-300 dark:placeholder:text-amber-700/50">
                                <button type="button" onclick="toggleTokenVisibility('inspectorBearerToken', this)" class="absolute right-2 text-amber-500/70 hover:text-amber-600 dark:hover:text-amber-400 transition-colors" title="Toggle Visibility">
                                    <svg class="w-4 h-4 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg class="w-4 h-4 eye-slash-icon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex-1 flex flex-col min-h-0 relative">
                            <label class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500 mb-1.5 flex justify-between items-center">
                                <span>Request Body / Params</span>
                            </label>
                            
                            <div class="flex gap-2 mb-2 shrink-0">
                                <button id="btnFormMode" onclick="setBodyMode('form')" class="mode-btn active text-[9px] px-2 py-1 rounded font-bold uppercase">Key-Value Form</button>
                                <button id="btnRawMode" onclick="setBodyMode('raw')" class="mode-btn text-[9px] px-2 py-1 rounded font-bold uppercase">Raw JSON</button>
                            </div>

                            <div id="formBuilder" class="flex-1 flex flex-col min-h-0 space-y-2 overflow-y-auto pr-1"></div>
                            <button id="addParamBtn" onclick="addParamRow()" class="mt-2 w-full py-1.5 border border-dashed border-gray-300 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white hover:border-gray-400 dark:hover:border-zinc-500 rounded text-[10px] font-bold transition-all shrink-0">+ Add Parameter</button>

                            <div id="rawBuilder" class="flex-1 flex flex-col min-h-0 hidden">
                                <textarea id="reqBody" class="code-input flex-1 h-full" spellcheck="false">{}</textarea>
                                <div class="flex justify-end gap-2 mt-2 shrink-0">
                                    <button onclick="clearJson()" class="text-[9px] font-bold text-gray-500 hover:text-gray-800 dark:hover:text-white uppercase transition-colors">Clear</button>
                                    <button onclick="formatJson()" class="text-[9px] font-bold text-laravel hover:text-red-600 uppercase transition-colors">Format</button>
                                </div>
                            </div>
                        </div>

                        <button onclick="executeRequest()" id="executeBtn" class="mt-4 w-full py-2.5 rounded-lg bg-gray-900 text-white dark:bg-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 font-bold text-xs tracking-wide transition-all flex justify-center items-center gap-2 shrink-0">
                            <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-horizontal-icon lucide-send-horizontal"><path d="M3.714 3.048a.498.498 0 0 0-.683.627l2.843 7.627a2 2 0 0 1 0 1.396l-2.842 7.627a.498.498 0 0 0 .682.627l18-8.5a.5.5 0 0 0 0-.904z"/><path d="M6 12h16"/></svg>    
                            SEND REQUEST
                        </button>
                    </div>

                    <div id="tab-code" class="p-5 hidden">
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500">cURL</span>
                                    <button onclick="copyCode('snippet-curl')" class="text-[9px] border border-gray-200 dark:border-zinc-700 px-2 py-0.5 rounded hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-500 dark:text-zinc-400">Copy</button>
                                </div>
                                <pre id="snippet-curl" class="code-input w-full overflow-x-auto"></pre>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-[9px] font-bold uppercase tracking-widest text-gray-500 dark:text-zinc-500">Fetch API</span>
                                    <button onclick="copyCode('snippet-fetch')" class="text-[9px] border border-gray-200 dark:border-zinc-700 px-2 py-0.5 rounded hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-500 dark:text-zinc-400">Copy</button>
                                </div>
                                <pre id="snippet-fetch" class="code-input w-full overflow-x-auto"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-[40%] flex flex-col bg-gray-50 dark:bg-zinc-950 z-10 border-l border-gray-200 dark:border-zinc-800">
            
            <div class="h-1/2 flex flex-col relative bg-white dark:bg-[#0a0a0a]">
                <div class="px-3 py-1.5 bg-gray-100 dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 flex justify-between items-center shrink-0">
                    <span class="text-[9px] font-bold uppercase tracking-widest flex items-center gap-1.5 text-gray-500 dark:text-zinc-400">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Visual Preview
                    </span>
                    <span id="previewMeta" class="text-[9px] font-mono font-bold text-gray-400 dark:text-zinc-600">WAITING</span>
                </div>
                <iframe id="previewFrame" class="flex-1 w-full border-0" sandbox="allow-same-origin allow-scripts allow-modals"></iframe>
            </div>

            <div class="h-1/2 flex flex-col relative border-t border-gray-200 dark:border-zinc-800 bg-white dark:bg-[#0a0a0a]">
                
                <div class="px-3 py-0 bg-gray-100 dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 flex justify-between items-center shrink-0">
                    <div class="flex items-center">
                        <button class="output-tab-btn active px-3 py-2 text-[9px] font-bold uppercase tracking-widest" onclick="switchOutputTab('raw')">
                            Raw Output
                        </button>
                        <button class="output-tab-btn px-3 py-2 text-[9px] font-bold uppercase tracking-widest flex items-center" onclick="switchOutputTab('console')">
                            Console <span id="consoleBadge" class="hidden ml-1.5 bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400 px-1.5 py-0.5 rounded-full text-[8px] leading-none">0</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="responseMeta" class="text-[9px] font-mono text-gray-500 dark:text-zinc-500"></span>
                        <span id="responseStatus" class="text-[9px] font-mono font-bold text-gray-500 dark:text-zinc-500 px-1.5 py-0.5 rounded border border-gray-200 dark:border-zinc-700">IDLE</span>
                    </div>
                </div>
                
                <div id="outputView-raw" class="flex-1 overflow-auto p-4 relative group block">
                    <button onclick="copyResponse()" class="absolute top-2 right-2 text-[9px] font-bold bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 px-2 py-1 rounded text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-colors z-10 opacity-0 group-hover:opacity-100">COPY</button>
                    <pre id="response" class="font-mono text-[11px] text-gray-600 dark:text-zinc-400 leading-relaxed outline-none break-all whitespace-pre-wrap">$ Request logs will appear here...</pre>
                </div>

                <div id="outputView-console" class="flex-1 overflow-hidden p-3 relative hidden flex-col">
                    <div class="flex justify-end mb-1 shrink-0">
                        <button onclick="clearConsole()" class="text-[9px] font-bold text-gray-400 dark:text-zinc-500 hover:text-red-500 transition-colors uppercase tracking-wider">Clear</button>
                    </div>
                    
                    <div id="consoleLogs" class="flex-1 overflow-y-auto font-mono text-[11px] flex flex-col gap-1 pb-2">
                        <div class="text-gray-400 dark:text-zinc-600 italic">Waiting for console output...</div>
                    </div>

                    <div class="mt-1 flex gap-2 shrink-0 border-t border-gray-100 dark:border-zinc-800 pt-2 items-center">
                        <span class="text-blue-500 dark:text-blue-400 font-bold text-[12px]">></span>
                        <input type="text" id="consoleInput" class="flex-1 bg-transparent text-gray-900 dark:text-zinc-200 font-mono text-[11px] outline-none placeholder:text-gray-300 dark:placeholder:text-zinc-700" placeholder="Type JavaScript and press Enter... (Use ↑/↓ for history)" autocomplete="off" spellcheck="false">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-2 pointer-events-none"></div>

<script>
const rows = Array.from(document.querySelectorAll('.route-row'));
const state = { method: 'all', mw: 'all', sortCol: null, sortDir: 1, activeRoute: null, bodyMode: 'form' };
let consoleMessageCount = 0;
let cmdHistory = [];
let cmdIndex = -1;

const consoleInjectorScript = `
<script>
    (function() {
        const originalConsole = window.console;
        function safeSerialize(obj, isLog = false) {
            if (typeof obj === 'undefined') return 'undefined';
            if (obj === null) return 'null';
            if (typeof obj === 'function') return 'ƒ ' + (obj.name || 'anonymous') + '()';
            if (typeof obj === 'string') return isLog ? obj : '"' + obj + '"';
            if (obj instanceof Error) return obj.name + ': ' + obj.message;
            if (typeof obj === 'object') {
                try {
                    return JSON.stringify(obj, function(k, v) {
                        if (typeof v === 'function') return 'ƒ()';
                        if (v === undefined) return 'undefined';
                        return v;
                    }, 2);
                } catch(e) { return Object.prototype.toString.call(obj); }
            }
            return String(obj);
        }
        function sendToParent(level, args) {
            const parsedArgs = args.map(a => safeSerialize(a, true));
            window.parent.postMessage({ type: 'iframe-console', level: level, args: parsedArgs }, '*');
        }
        window.console = {
            ...originalConsole,
            log: function(...args) { sendToParent('log', args); originalConsole.log.apply(this, args); },
            warn: function(...args) { sendToParent('warn', args); originalConsole.warn.apply(this, args); },
            error: function(...args) { sendToParent('error', args); originalConsole.error.apply(this, args); },
            info: function(...args) { sendToParent('info', args); originalConsole.info.apply(this, args); }
        };
        window.onerror = function(msg, url, line) {
            sendToParent('error', [msg, 'Line: ' + line]);
            return false;
        };
        window.addEventListener('message', function(e) {
            if(e.data && e.data.type === 'eval-code') {
                try {
                    let result = window.eval(e.data.code);
                    window.parent.postMessage({ type: 'iframe-console', level: 'eval-result', args: [safeSerialize(result, false)] }, '*');
                } catch(err) {
                    window.parent.postMessage({ type: 'iframe-console', level: 'error', args: [err.name + ': ' + err.message] }, '*');
                }
            }
            // ─── YENİ: Iframe Tema Senkronizasyonu Dinleyicisi ───
            if(e.data && e.data.type === 'theme-sync') {
                if (e.data.theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        });
        
        // İlk Yüklemede Temayı Al
        try {
            if (window.parent.document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.add('dark');
            }
        } catch(err) {}
    })();
<\/script>`;

function init() {
    buildMethodFilters();
    buildMwFilter();
    buildStats();
    initPreview();
    clearJson(); 
    
    const themeBtn = document.getElementById('themeToggle');
    themeBtn.addEventListener('click', () => {
        let newTheme = 'light';
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
            newTheme = 'dark';
        }
        localStorage.setItem('laravel_explorer_theme', newTheme);
        
        // ─── YENİ: Temayı Iframe'e Gönder ───
        const iframe = document.getElementById('previewFrame');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({ type: 'theme-sync', theme: newTheme }, '*');
        }
    });

    const headerTokenInput = document.getElementById('headerBearerToken');
    const inspectorTokenInput = document.getElementById('inspectorBearerToken');
    const savedToken = localStorage.getItem('laravel_explorer_token') || '';
    
    headerTokenInput.value = savedToken;
    inspectorTokenInput.value = savedToken;

    function syncTokenState(val) {
        const cleanToken = val.trim();
        localStorage.setItem('laravel_explorer_token', cleanToken);
        headerTokenInput.value = cleanToken;
        inspectorTokenInput.value = cleanToken;
        if(state.activeRoute) generateSnippets();
    }

    headerTokenInput.addEventListener('input', (e) => syncTokenState(e.target.value));
    inspectorTokenInput.addEventListener('input', (e) => syncTokenState(e.target.value));

    window.addEventListener('keydown', e => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); document.getElementById('search').focus(); }
        if (e.key === 'Escape') closeDetail();
    });
    document.getElementById('search').addEventListener('input', filterData);

    const consoleInput = document.getElementById('consoleInput');
    consoleInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const code = this.value.trim();
            if (!code) return;
            if (cmdHistory[cmdHistory.length - 1] !== code) cmdHistory.push(code);
            cmdIndex = cmdHistory.length;
            appendConsoleLog('input-echo', [code]); 
            const iframe = document.getElementById('previewFrame');
            if(iframe.contentWindow) iframe.contentWindow.postMessage({ type: 'eval-code', code: code }, '*');
            this.value = '';
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (cmdIndex > 0) { cmdIndex--; this.value = cmdHistory[cmdIndex]; }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (cmdIndex < cmdHistory.length - 1) { cmdIndex++; this.value = cmdHistory[cmdIndex]; } 
            else { cmdIndex = cmdHistory.length; this.value = ''; }
        }
    });
}

function toggleTokenVisibility(inputId, btnElement) {
    const input = document.getElementById(inputId);
    const eyeIcon = btnElement.querySelector('.eye-icon');
    const eyeSlashIcon = btnElement.querySelector('.eye-slash-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeSlashIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeSlashIcon.classList.add('hidden');
    }
}

window.addEventListener('message', (e) => {
    if (e.data && e.data.type === 'iframe-console') {
        appendConsoleLog(e.data.level, e.data.args);
    }
});

function appendConsoleLog(level, args) {
    const container = document.getElementById('consoleLogs');
    if (consoleMessageCount === 0 && level !== 'input-echo') container.innerHTML = ''; 
    if (container.innerHTML.includes('Waiting for console output')) container.innerHTML = '';
    
    const msg = args.join(' ');
    let colorCls = 'text-gray-700 dark:text-zinc-300';
    let icon = '';
    
    if (level === 'error') { colorCls = 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-500/10 px-2 py-1 rounded mt-1'; icon = '✖ '; }
    else if (level === 'warn') { colorCls = 'text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-500/10 px-2 py-1 rounded mt-1'; icon = '⚠ '; }
    else if (level === 'input-echo') { colorCls = 'text-blue-600 dark:text-blue-400 font-bold mt-2'; icon = '› '; } 
    else if (level === 'eval-result') { colorCls = 'text-gray-400 dark:text-zinc-500 italic'; icon = '← '; }
    else { icon = '  '; } 

    const div = document.createElement('div');
    div.className = `console-log break-all whitespace-pre-wrap ${colorCls}`;
    div.textContent = icon + msg;
    container.appendChild(div);

    if(level !== 'input-echo' && level !== 'eval-result') {
        consoleMessageCount++;
        const badge = document.getElementById('consoleBadge');
        badge.textContent = consoleMessageCount;
        badge.classList.remove('hidden');
    }
    
    container.scrollTop = container.scrollHeight;
}

function clearConsole() {
    document.getElementById('consoleLogs').innerHTML = '<div class="text-gray-400 dark:text-zinc-600 italic">Waiting for console output...</div>';
    consoleMessageCount = 0;
    document.getElementById('consoleBadge').classList.add('hidden');
}

function switchOutputTab(tab) {
    document.querySelectorAll('.output-tab-btn').forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById('outputView-raw').style.display = tab === 'raw' ? 'block' : 'none';
    document.getElementById('outputView-console').style.display = tab === 'console' ? 'flex' : 'none';
}

function buildMethodFilters() {
    const methods = ['all', 'GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
    const container = document.getElementById('methodFilters');
    methods.forEach(m => {
        const btn = document.createElement('button');
        btn.textContent = m.toUpperCase();
        btn.className = `px-2 py-1 rounded-full border text-[9px] font-bold uppercase tracking-wider transition-all ${m === 'all' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 border-transparent' : 'bg-transparent text-gray-500 border-gray-200 dark:text-zinc-400 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800'}`;
        btn.onclick = () => {
            state.method = m;
            Array.from(container.children).forEach(c => {
                c.className = `px-2 py-1 rounded-full border text-[9px] font-bold uppercase tracking-wider transition-all ${c.textContent.toLowerCase() === m.toLowerCase() ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 border-transparent' : 'bg-transparent text-gray-500 border-gray-200 dark:text-zinc-400 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800'}`;
            });
            filterData();
        };
        container.appendChild(btn);
    });
}

function buildMwFilter() {
    const mwSet = new Set();
    rows.forEach(r => r.dataset.middleware.split(',').filter(Boolean).forEach(m => mwSet.add(m.trim())));
    const select = document.getElementById('middlewareFilter');
    Array.from(mwSet).sort().forEach(mw => select.add(new Option(mw, mw)));
    select.addEventListener('change', e => { state.mw = e.target.value; filterData(); });
}

function buildStats() {
    const counts = {};
    rows.forEach(r => r.dataset.methods.split(',').forEach(m => counts[m] = (counts[m]||0)+1));
    const container = document.getElementById('headerStats');
    container.innerHTML = Object.entries(counts).filter(([m]) => m !== 'head').map(([m,c]) => `<span class="text-gray-500 dark:text-zinc-500 uppercase tracking-widest">${m} <span class="text-gray-900 dark:text-white font-bold ml-0.5">${c}</span></span>`).join('<span class="text-gray-300 dark:text-zinc-700 mx-1">|</span>');
}

function filterData() {
    const q = document.getElementById('search').value.toLowerCase().split(/\s+/).filter(Boolean);
    let vis = 0;
    rows.forEach(r => {
        const text = Object.values(r.dataset).join(' ').toLowerCase();
        const m1 = q.every(t => text.includes(t));
        const m2 = state.method === 'all' || r.dataset.methods.includes(state.method.toLowerCase());
        const m3 = state.mw === 'all' || r.dataset.middleware.includes(state.mw.toLowerCase());
        const show = m1 && m2 && m3;
        r.style.display = show ? '' : 'none';
        if(show) vis++;
    });
    document.getElementById('statsVisible').textContent = vis;
    document.getElementById('statsTotal').textContent = rows.length;
    document.getElementById('emptyState').style.display = vis === 0 ? 'flex' : 'none';
}

function toggleSort(col) {
    state.sortDir *= -1;
    const tbody = document.getElementById('routeTableBody');
    rows.sort((a,b) => (a.dataset[col]||'').localeCompare(b.dataset[col]||'') * state.sortDir).forEach(r => tbody.appendChild(r));
}

function openDetail(row) {
    document.querySelectorAll('.route-row.active').forEach(r => r.classList.remove('active'));
    row.classList.add('active');
    
    state.activeRoute = {
        uri: row.dataset.uri,
        methods: row.dataset.methods.split(','),
        name: row.dataset.name,
        action: row.dataset.action,
        mw: row.dataset.middleware.split(',').filter(Boolean),
        paramsRaw: row.dataset.params || '[]',
        isAuth: row.dataset.auth === 'true'
    };

    const authContainer = document.getElementById('inspectorAuthContainer');
    if(state.activeRoute.isAuth) {
        authContainer.classList.remove('hidden');
        document.getElementById('inspectorBearerToken').value = localStorage.getItem('laravel_explorer_token') || '';
        document.getElementById('inspectorBearerToken').type = 'password';
        document.querySelector('#inspectorAuthContainer .eye-icon').classList.remove('hidden');
        document.querySelector('#inspectorAuthContainer .eye-slash-icon').classList.add('hidden');
    } else {
        authContainer.classList.add('hidden');
    }

    let authBadge = state.activeRoute.isAuth 
        ? `<span class="bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-500 border border-amber-200 dark:border-amber-500/20 px-1.5 py-0.5 rounded flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider shrink-0" title="Requires Authentication"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Auth Required</span>`
        : '';
    
    document.getElementById('insUrl').innerHTML = `<div class="flex items-center justify-between gap-2"><span class="break-all font-mono">/${state.activeRoute.uri.replace(/^\//,'')}</span>${authBadge}</div>`;
    document.getElementById('insMethods').innerHTML = state.activeRoute.methods.map(m => `<span class="badge badge-${m.toLowerCase()}">${m.toUpperCase()}</span>`).join('');
    document.getElementById('insName').innerHTML = state.activeRoute.name ? `<span class="text-gray-900 dark:text-gray-200">${state.activeRoute.name}</span>` : `<span class="text-gray-400 dark:text-zinc-500 italic">No Name Assigned</span>`;
    
    if (state.activeRoute.action === 'closure') {
        document.getElementById('insAction').innerHTML = `<span class="badge bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 gap-1 mt-0.5"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg> Anonymous Function (Closure)</span>`;
    } else {
        document.getElementById('insAction').textContent = state.activeRoute.action;
    }
    document.getElementById('insMiddleware').innerHTML = state.activeRoute.mw.map(m => `<span class="text-[9px] px-2 py-1 rounded bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 border border-gray-200 dark:border-zinc-700">${m}</span>`).join('');

    let paramsHtml = ''; let formBuilderHtml = '';
    const pathMatch = state.activeRoute.uri.match(/\{([^}]+)\}/g);
    if (pathMatch) {
        pathMatch.forEach(p => {
            const clean = p.replace(/[{}]/g, ''); const isOptional = clean.endsWith('?'); const name = clean.replace('?', '');
            paramsHtml += `<div class="flex justify-between items-center bg-gray-50 dark:bg-zinc-800/50 px-2 py-1.5 rounded border border-gray-200 dark:border-white/5"><span class="text-[10px] font-mono text-blue-600 dark:text-blue-400">${name}</span><div class="flex gap-2 items-center"><span class="text-[8px] text-gray-500 border border-gray-300 dark:border-zinc-700 rounded px-1 uppercase">Path</span><span class="text-[8px] font-bold ${isOptional ? 'text-gray-400 dark:text-zinc-500' : 'text-red-500 dark:text-red-400'}">${isOptional ? 'OPTIONAL' : 'REQUIRED'}</span></div></div>`;
        });
    }

    try {
        const backendParams = JSON.parse(state.activeRoute.paramsRaw);
        if(Array.isArray(backendParams)) {
            backendParams.forEach(p => {
                paramsHtml += `<div class="flex justify-between items-center bg-gray-50 dark:bg-zinc-800/50 px-2 py-1.5 rounded border border-gray-200 dark:border-white/5"><span class="text-[10px] font-mono text-blue-600 dark:text-blue-400">${p.name}</span><div class="flex gap-2 items-center"><span class="text-[8px] text-gray-500 border border-gray-300 dark:border-zinc-700 rounded px-1 uppercase">${p.type || 'Body/Query'}</span><span class="text-[8px] font-bold ${p.required ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-zinc-500'}">${p.required ? 'REQUIRED' : 'OPTIONAL'}</span></div></div>`;
                formBuilderHtml += `<div class="flex gap-2 param-row shrink-0"><input type="text" placeholder="Key" value="${p.name}" class="w-2/5 code-input py-1.5 px-2"><input type="text" placeholder="Value" value="" class="flex-1 code-input py-1.5 px-2"><button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-600 dark:hover:text-red-400 px-1 transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>`;
            });
        }
    } catch(e) {}

    document.getElementById('insParams').innerHTML = paramsHtml || '<div class="text-[10px] text-gray-500 italic">No parameters detected.</div>';
    
    const formBuilder = document.getElementById('formBuilder');
    if (formBuilderHtml) formBuilder.innerHTML = formBuilderHtml;
    else { formBuilder.innerHTML = ''; addParamRow(); }

    const select = document.getElementById('reqMethod');
    select.innerHTML = state.activeRoute.methods.map(m => `<option value="${m.toUpperCase()}">${m.toUpperCase()}</option>`).join('');
    if(state.activeRoute.methods.includes('post')) select.value = 'POST';

    generateSnippets();
    document.getElementById('inspectorPanel').classList.add('open');
}

function closeDetail() {
    document.getElementById('inspectorPanel').classList.remove('open');
    document.querySelectorAll('.route-row.active').forEach(r => r.classList.remove('active'));
    state.activeRoute = null;
}

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('[id^="tab-"]').forEach(d => d.style.display = 'none');
    event.target.classList.add('active');
    document.getElementById(`tab-${tab}`).style.display = 'block';
}

function setBodyMode(mode) {
    state.bodyMode = mode;
    document.getElementById('btnFormMode').classList.toggle('active', mode === 'form');
    document.getElementById('btnRawMode').classList.toggle('active', mode === 'raw');
    document.getElementById('formBuilder').style.display = mode === 'form' ? 'flex' : 'none';
    document.getElementById('addParamBtn').style.display = mode === 'form' ? 'block' : 'none';
    document.getElementById('rawBuilder').style.display = mode === 'raw' ? 'flex' : 'none';
}

function addParamRow(key = '', val = '') {
    const container = document.getElementById('formBuilder');
    const row = document.createElement('div'); row.className = 'flex gap-2 param-row shrink-0';
    row.innerHTML = `<input type="text" placeholder="Key" value="${key}" class="w-2/5 code-input py-1.5 px-2"><input type="text" placeholder="Value" value="${val}" class="flex-1 code-input py-1.5 px-2"><button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-600 dark:hover:text-red-400 px-1 transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>`;
    container.appendChild(row);
}

function getRequestData() {
    if (state.bodyMode === 'raw') {
        try { return JSON.stringify(JSON.parse(document.getElementById('reqBody').value)); } 
        catch (e) { return document.getElementById('reqBody').value; }
    }
    const obj = {};
    document.querySelectorAll('.param-row').forEach(row => {
        const inputs = row.querySelectorAll('input');
        const k = inputs[0].value.trim(); const v = inputs[1].value;
        if (k) obj[k] = v;
    });
    return JSON.stringify(obj);
}

function formatJson() {
    try {
        const el = document.getElementById('reqBody');
        el.value = JSON.stringify(JSON.parse(el.value), null, 2);
    } catch(e) { showToast('Invalid JSON syntax', 'error'); }
}

function clearJson() { document.getElementById('reqBody').value = '{\n\n}'; document.getElementById('formBuilder').innerHTML = ''; addParamRow(); }

function generateSnippets() {
    if(!state.activeRoute) return;
    const url = location.origin + '/' + state.activeRoute.uri.replace(/^\//,'');
    const m = document.getElementById('reqMethod') ? document.getElementById('reqMethod').value : state.activeRoute.methods[0].toUpperCase();
    const csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    const bearerToken = localStorage.getItem('laravel_explorer_token') || '';
    
    let headersCurl = `  -H "Accept: application/json" \\\n  -H "Content-Type: application/json"`;
    let headersFetch = `    'Accept': 'application/json',\n    'Content-Type': 'application/json'`;
    
    if(csrf) { headersCurl += ` \\\n  -H "X-CSRF-TOKEN: ${csrf}"`; headersFetch += `,\n    'X-CSRF-TOKEN': '${csrf}'`; }
    if(bearerToken) { headersCurl += ` \\\n  -H "Authorization: Bearer ${bearerToken}"`; headersFetch += `,\n    'Authorization': 'Bearer ${bearerToken}'`; }
    
    document.getElementById('snippet-curl').textContent = `curl -X ${m} "${url}" \\\n${headersCurl}`;
    document.getElementById('snippet-fetch').textContent = `fetch('${url}', {\n  method: '${m}',\n  headers: {\n${headersFetch}\n  }\n})\n.then(res => res.json())\n.then(console.log);`;
}

document.getElementById('reqMethod').addEventListener('change', generateSnippets);

async function executeRequest() {
    if(!state.activeRoute) return;
    clearConsole(); 
    
    const btn = document.getElementById('executeBtn');
    const method = document.getElementById('reqMethod').value;
    const url = location.origin + '/' + state.activeRoute.uri.replace(/^\//,'');
    const bodyStr = getRequestData();
    
    btn.disabled = true; btn.innerHTML = `<span class="animate-pulse flex items-center justify-center gap-2"><svg class="w-3 h-3 animate-spin" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-icon lucide-loader"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg> SENDING...</span>`;
    
    const output = document.getElementById('response');
    const statusEl = document.getElementById('responseStatus');
    
    output.textContent = `> [${method}] ${url}\n> Payload: ${bodyStr}\n> Sending request...`;
    statusEl.textContent = 'WAIT';
    statusEl.className = 'text-[10px] font-mono font-bold text-amber-600 dark:text-amber-500 border border-amber-300 dark:border-amber-500/50 px-1.5 py-0.5 rounded animate-pulse';

    const opts = { method, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
    const csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    const bearerToken = localStorage.getItem('laravel_explorer_token') || '';
    if (csrf) opts.headers['X-CSRF-TOKEN'] = csrf;
    if (bearerToken) opts.headers['Authorization'] = `Bearer ${bearerToken}`;
    if(['POST','PUT','PATCH'].includes(method)) { opts.headers['Content-Type'] = 'application/json'; opts.body = bodyStr; }

    const t0 = performance.now();
    try {
        const res = await fetch(url, opts);
        const ms = Math.round(performance.now() - t0);
        const ct = res.headers.get('content-type') || '';
        const isJson = ct.includes('application/json');
        const data = isJson ? await res.json() : await res.text();

        statusEl.textContent = res.status;
        statusEl.className = `text-[10px] font-mono font-bold border px-1.5 py-0.5 rounded ${res.ok ? 'text-green-600 bg-green-50 border-green-200 dark:bg-transparent dark:text-emerald-400 dark:border-emerald-400/50' : 'text-red-600 bg-red-50 border-red-200 dark:bg-transparent dark:text-rose-400 dark:border-rose-400/50'}`;
        document.getElementById('responseMeta').textContent = `${ms}ms`;

        if(isJson) output.innerHTML = syntaxHighlight(JSON.stringify(data, null, 2));
        else output.innerHTML = highlightHTML(String(data));
        
        const pf = document.getElementById('previewFrame');
        if(isJson) {
            pf.srcdoc = consoleInjectorScript + `<html style="background:transparent"><pre style="color:inherit;padding:15px;font-family:monospace;font-size:12px;white-space:pre-wrap;">${escapeHtml(JSON.stringify(data, null, 2))}</pre></html>`;
            document.getElementById('previewMeta').textContent = 'JSON RENDER';
            document.getElementById('previewMeta').className = 'text-[9px] font-mono font-bold text-green-600 dark:text-emerald-500';
        } else {
            pf.srcdoc = consoleInjectorScript + data;
            document.getElementById('previewMeta').textContent = 'HTML RENDER';
            document.getElementById('previewMeta').className = 'text-[9px] font-mono font-bold text-blue-600 dark:text-blue-500';
        }

        showToast(`Done (${ms}ms)`, res.ok ? 'success' : 'error');
    } catch(err) {
        statusEl.textContent = 'ERR';
        statusEl.className = 'text-[10px] font-mono font-bold text-red-600 bg-red-50 border border-red-200 dark:bg-transparent dark:text-rose-500 dark:border-rose-500/50 px-1.5 py-0.5 rounded';
        output.textContent = `> Request failed\n> ${err.message}`;
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-horizontal-icon lucide-send-horizontal"><path d="M3.714 3.048a.498.498 0 0 0-.683.627l2.843 7.627a2 2 0 0 1 0 1.396l-2.842 7.627a.498.498 0 0 0 .682.627l18-8.5a.5.5 0 0 0 0-.904z"/><path d="M6 12h16"/></svg> SEND REQUEST`;
    }
}

// ─── UTILS ───
function initPreview() {
    const isDark = document.documentElement.classList.contains('dark');
    const htmlClass = isDark ? 'class="dark"' : '';
    document.getElementById('previewFrame').srcdoc = consoleInjectorScript + `<html ${htmlClass} style="display:flex;align-items:center;justify-content:center;font-family:sans-serif;font-size:11px;font-weight:bold;letter-spacing:0.1em;text-transform:uppercase;color:#9ca3af;">Waiting for request...</html>`;
    document.getElementById('previewMeta').textContent = 'WAITING';
    document.getElementById('previewMeta').className = 'text-[9px] font-mono font-bold text-gray-400 dark:text-zinc-500';
}

function syntaxHighlight(json) {
    if (typeof json != 'string') json = JSON.stringify(json, undefined, 2);
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, match => {
        let cls = 'text-amber-600 dark:text-amber-300';
        if (/^"/.test(match)) cls = /:$/.test(match) ? 'text-blue-600 dark:text-cyan-400' : 'text-green-600 dark:text-emerald-400';
        else if (/true|false|null/.test(match)) cls = 'text-purple-600 dark:text-violet-400';
        return `<span class="${cls}">${match}</span>`;
    });
}

function highlightHTML(html) {
    let escaped = escapeHtml(html);
    escaped = escaped.replace(/&lt;!--[\s\S]*?--&gt;/g, match => `<span class="text-gray-400 dark:text-zinc-500 italic">${match}</span>`);
    escaped = escaped.replace(/&lt;(\/?)([a-zA-Z0-9\-:]+)(.*?)&gt;/g, (match, slash, tagName, rest) => {
        let attrs = rest.replace(/([a-zA-Z0-9\-:]+)=(".*?"|'.*?'|[^\s>]+)/g, (m, attrName, attrVal) => {
            return ` <span class="text-blue-600 dark:text-emerald-300">${attrName}</span>=<span class="text-amber-600 dark:text-amber-300">${attrVal}</span>`;
        });
        return `<span class="text-gray-400 dark:text-zinc-500">&lt;${slash}</span><span class="text-red-500 dark:text-pink-400">${tagName}</span>${attrs}<span class="text-gray-400 dark:text-zinc-500">&gt;</span>`;
    });
    return escaped;
}

function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

async function copyCode(id) { copyToClipboard(document.getElementById(id).textContent || document.getElementById(id).innerText); }
async function copyResponse() { copyToClipboard(document.getElementById('response').textContent || document.getElementById('response').innerText); }

function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(() => showToast('Copied to clipboard!', 'success')).catch(() => fallbackCopy(text));
    else fallbackCopy(text);
}

function fallbackCopy(text) {
    const textArea = document.createElement("textarea"); textArea.value = text; textArea.style.position = "fixed"; textArea.style.left = "-999999px";
    document.body.appendChild(textArea); textArea.focus(); textArea.select();
    try { document.execCommand('copy'); showToast('Copied to clipboard!', 'success'); } catch (err) { showToast('Copy failed', 'error'); }
    document.body.removeChild(textArea);
}

function showToast(msg, type='info') {
    const bg = type==='error' ? 'bg-red-600' : 'bg-gray-900 dark:bg-white';
    const textCol = type==='error' ? 'text-white' : 'text-white dark:text-gray-900';
    const t = document.createElement('div');
    t.className = `${bg} ${textCol} px-4 py-2.5 rounded-lg text-xs font-bold shadow-xl shadow-black/10 dark:shadow-black/50 animate-fade-in pointer-events-auto flex items-center gap-2`;
    t.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> ${msg}`;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity 0.3s'; setTimeout(()=>t.remove(), 300); }, 2500);
}

document.getElementById('exportBtn').onclick = () => {
    const data = rows.filter(r=>r.style.display!=='none').map(r=>({...r.dataset}));
    const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(new Blob([JSON.stringify(data, null, 2)])), download: 'routes.json' });
    a.click(); showToast('Exported successfully!', 'success');
};

init();
</script>
<script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>