<?php
/**
 * El Celler Nou - Router Class
 * Sistema de enrutamiento profesional
 */

class Router
{
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static ?string $currentRoute = null;
    private static array $params = [];
    private static string $basePath = '';

    /**
     * Registrar una ruta GET
     */
    public static function get(string $pattern, string $handler, ?string $name = null): void
    {
        self::addRoute('GET', $pattern, $handler, $name);
    }

    /**
     * Registrar una ruta POST
     */
    public static function post(string $pattern, string $handler, ?string $name = null): void
    {
        self::addRoute('POST', $pattern, $handler, $name);
    }

    /**
     * Añadir ruta al registro
     */
    private static function addRoute(string $method, string $pattern, string $handler, ?string $name): void
    {
        $route = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'name' => $name,
            'regex' => self::patternToRegex($pattern)
        ];

        self::$routes[] = $route;

        if ($name) {
            self::$namedRoutes[$name] = $route;
        }
    }

    /**
     * Convertir patrón de ruta a expresión regular
     */
    private static function patternToRegex(string $pattern): string
    {
        $regex = preg_quote($pattern, '/');
        $regex = preg_replace('/\\\{([a-zA-Z_]+)\\\}/', '(?P<$1>[^\/]+)', $regex);
        $regex = preg_replace('/\\\{([a-zA-Z_]+)\\\?\\\}/', '(?:\/(?P<$1>[^\/]+))?', $regex);
        return '/^' . $regex . '\/?$/';
    }

    /**
     * Resolver la ruta actual
     */
    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = self::getUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                self::$currentRoute = $route['name'];

                // Extraer parámetros nombrados
                self::$params = array_filter($matches, function ($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                // Limpiar parámetros vacíos
                self::$params = array_filter(self::$params, function ($value) {
                    return $value !== '';
                });

                // Cargar el handler
                self::loadHandler($route['handler']);
                return;
            }
        }

        // 404 - No encontrado
        self::notFound();
    }

    /**
     * Obtener URI limpia
     */
    private static function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Remover query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remover base path
        if (strpos($uri, self::$basePath) === 0) {
            $uri = substr($uri, strlen(self::$basePath));
        }

        // Asegurar que empieza con /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        // Remover trailing slash excepto para /
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Cargar el archivo handler
     */
    private static function loadHandler(string $handler): void
    {
        $file = ROOT_PATH . '/controllers/' . $handler . '.php';

        if (file_exists($file)) {
            extract(self::$params);
            require $file;
        } else {
            self::notFound();
        }
    }

    /**
     * Página 404
     */
    private static function notFound(): void
    {
        http_response_code(404);
        require ROOT_PATH . '/controllers/404.php';
    }

    /**
     * Obtener parámetro de la ruta
     */
    public static function param(string $name, $default = null)
    {
        return self::$params[$name] ?? $default;
    }

    /**
     * Obtener todos los parámetros
     */
    public static function params(): array
    {
        return self::$params;
    }

    /**
     * Generar URL a partir del nombre de la ruta
     */
    public static function url(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            return '/';
        }

        $pattern = self::$namedRoutes[$name]['pattern'];

        foreach ($params as $key => $value) {
            $pattern = str_replace('{' . $key . '}', $value, $pattern);
            $pattern = str_replace('{' . $key . '?}', $value, $pattern);
        }

        $pattern = preg_replace('/\{[a-zA-Z_]+\?\}/', '', $pattern);
        $pattern = preg_replace('/\/+/', '/', $pattern);
        $pattern = rtrim($pattern, '/');

        // Asegurar que siempre devuelve al menos '/'
        if (empty($pattern)) {
            return '/';
        }

        return $pattern;
    }

    /**
     * Obtener nombre de la ruta actual
     */
    public static function currentRoute(): ?string
    {
        return self::$currentRoute;
    }

    /**
     * Verificar si la ruta actual coincide
     */
    public static function is(string $name): bool
    {
        return self::$currentRoute === $name;
    }

    /**
     * Redirección
     */
    public static function redirect(string $url, int $code = 302): void
    {
        header('Location: ' . $url, true, $code);
        exit;
    }

    /**
     * Redirección a ruta nombrada
     */
    public static function redirectTo(string $name, array $params = [], int $code = 302): void
    {
        self::redirect(self::url($name, $params), $code);
    }

    /**
     * Establecer base path (para subdirectorios)
     */
    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }
}
