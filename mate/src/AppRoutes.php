<?php

namespace Mate;

use Symfony\AI\Mate\Attribute\MateTool;
use Symfony\AI\Mate\Encoding\ResponseEncoder;
use Symfony\Component\Process\Process;

class AppRoutes
{
    /**
     * @param string|null $path Filtra por fragmento de ruta, por ejemplo "/catalog"
     */
    #[MateTool(
        name: 'app-routes',
        title: 'App Routes',
        description: 'List the application routes (name, path, methods) using bin/console debug:router, optionally filtered by a path fragment',
    )]
    public function list(?string $path = null): string
    {
        $process = new Process(
            [\PHP_BINARY, 'bin/console', 'debug:router', '--format=json', '--env=dev'],
            \dirname(__DIR__, 2),
        );
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            return ResponseEncoder::encodeUntrusted(['error' => trim($process->getErrorOutput())]);
        }

        $routes = json_decode($process->getOutput(), true, flags: \JSON_THROW_ON_ERROR);

        $result = [];
        foreach ($routes as $name => $route) {
            if (str_starts_with($name, '_')) {
                continue; // rutas internas del framework y del profiler
            }
            if (null !== $path && !str_contains($route['path'], $path)) {
                continue;
            }
            $result[] = [
                'name' => $name,
                'path' => $route['path'],
                'methods' => $route['method'],
            ];
        }

        return ResponseEncoder::encodeUntrusted(['routes' => $result, 'count' => \count($result)]);
    }
}
