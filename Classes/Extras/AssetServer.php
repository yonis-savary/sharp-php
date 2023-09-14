<?php

namespace Sharp\Classes\Extras;

use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Core\Autoloader;

class AssetServer
{
    use Component, Configurable;

    const CACHE_KEY = "sharp.asset-server.path-index";

    const EXTENSIONS_MIMES = [
        "js" => "application/javascript",
        "css" => "text/css"
    ];

    protected $cacheIndex = [];

    public static function getDefaultConfiguration(): array
    {
        return [
            "enabled" => true,
            "path" => "/assets",
            "cached" => false,
            "middlewares" => [],
            "max-age" => false
        ];
    }

    public static function initialize()
    {
        self::getInstance()->handleIfEnabled();
    }

    public function __construct()
    {
        $this->getConfiguration();

        $this->cacheIndex = $this->configuration["cached"] ?
            Cache::getInstance()->getReference(self::CACHE_KEY):
            [];
    }

    public function handleIfEnabled()
    {
        if (!$this->isEnabled())
            return;

        $req = Request::buildFromGlobals();
        $this->handleRequest($req);
    }

    /**
     * Find an asset absolute path from its path end
     *
     * @param string $assetName Requested asset name path's end
     * @return string|false Absolute asset's path or false if not found
     */
    public function findAsset(string $assetName): string|false
    {
        if ($path = $this->cacheIndex[$assetName] ?? false)
            return $path;

        foreach (Autoloader::getListFiles(Autoloader::ASSETS) as $file)
        {
            if (!str_ends_with($file, $assetName))
                continue;

            $this->cacheIndex[$assetName] = $file;

            return $file;
        }
        return false;
    }

    /**
     * @param string $assetName Requested asset name path's end
     * @return string An URL that will work with the assetServer internal route
     */
    public function getURL(string $assetName): string
    {
        $routePath = $this->configuration["path"];
        $assetName = urlencode($assetName);
        return "$routePath?file=$assetName";
    }

    public function handleRequest(Request $req, bool $returnResponse=false) : Response|false
    {
        $routePath = $this->configuration["path"];
        $middlewares = $this->configuration["middlewares"];
        $selfRoute = Route::get($routePath, fn($req) => $this->serve($req), $middlewares);

        if (!$selfRoute->match($req))
            return false;

        $response = Response::adapt($selfRoute($req));

        if ($returnResponse)
            return $response;

        $response->display();
        die;
    }

    protected function serve(Request $req): Response
    {
        if (!($searchedFile = $req->params("file") ?? false))
            return Response::json("A 'file' parameter is needed", 401);

        if (!($path = $this->findAsset($searchedFile)))
            return Response::json("Asset [$searchedFile] not found", 404);

        $res = Response::file($path);
        if ($cacheTime = $this->configuration["max-age"])
            $res->withHeaders(["Cache-Control" => "max-age=$cacheTime"]);

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (array_key_exists($extension, self::EXTENSIONS_MIMES))
            $res->withHeaders(["Content-Type" => self::EXTENSIONS_MIMES[$extension]]);

        return $res;
    }
}