<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class LynxSidebarMenu
{
    public static function items(): array
    {
        $items = config('lynx_sidebar.items', []);

        return self::normalizeItems($items);
    }

    protected static function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! self::itemVisible($item)) {
                continue;
            }

            $children = self::normalizeItems(Arr::get($item, 'children', []));
            $item['children'] = $children;
            $item['has_children'] = ! empty($children);
            $item['url'] = self::resolveUrl($item);
            $item['is_active'] = self::itemActive($item, $children);
            $item['is_expanded'] = $item['has_children'] && $item['is_active'];
            $item['id'] = Arr::get($item, 'key', 'item-' . $index);
            $item['is_leaf'] = ! $item['has_children'];

            if (! $item['has_children'] && empty($item['url'])) {
                continue;
            }

            if ($item['has_children'] || ! empty($item['url'])) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }

    protected static function itemVisible(array $item): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if (! empty($item['types']) && ! in_array($user->type, (array) $item['types'], true)) {
            return false;
        }

        if (! empty($item['show_when'])) {
            $method = $item['show_when'];

            if (! method_exists($user, $method) || ! $user->{$method}()) {
                return false;
            }
        }

        if (! empty($item['permissions'])) {
            $permissions = (array) $item['permissions'];

            $allowed = false;
            foreach ($permissions as $permission) {
                if ($user->can($permission)) {
                    $allowed = true;
                    break;
                }
            }

            if (! $allowed) {
                return false;
            }
        }

        return true;
    }

    protected static function resolveUrl(array $item): ?string
    {
        $routeName = Arr::get($item, 'route_name');

        if ($routeName && Route::has($routeName)) {
            $params = Arr::get($item, 'route_params', []);

            return route($routeName, $params);
        }

        $url = Arr::get($item, 'url');

        if (! empty($url)) {
            return url($url);
        }

        return null;
    }

    protected static function itemActive(array $item, array $children = []): bool
    {
        if (! empty($children)) {
            foreach ($children as $child) {
                if (! empty($child['is_active']) || ! empty($child['is_expanded'])) {
                    return true;
                }
            }
        }

        $routePatterns = (array) Arr::get($item, 'route_is', []);
        if (! empty($routePatterns) && request()->routeIs(...$routePatterns)) {
            return true;
        }

        $pathPatterns = (array) Arr::get($item, 'path_is', []);
        foreach ($pathPatterns as $pattern) {
            if (request()->is($pattern)) {
                return true;
            }
        }

        $routeName = Arr::get($item, 'route_name');
        if ($routeName && request()->routeIs($routeName)) {
            return true;
        }

        return false;
    }
}
