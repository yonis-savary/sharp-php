<?php

namespace Sharp\Extensions\LazySearch\Components;

use Exception;
use PDO;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Storage;
use Sharp\Classes\Web\Renderer;
use Sharp\Core\Utils;

class LazySearch
{
    use Component, Configurable;

    public static function getDefaultConfiguration(): array
    {
        return [
            'locale' => 'en',
            'ignore_links' => true,
            'template' => null,
            'size_limit' => 100,
            'export_middlewares' => [],
            'export_chunk_size' => 20_000,
            'cached' => false,
            'cache_time_to_live' => 3600*2,
            'default_fetch_possibilities' => true
        ];
    }

    /**
     * This class has three modes
     * Form : Return a webpage containing the LazySearch table
     * Data : Return JSON data to fill the LazySearch table
     * File : Stream a CSV file containing filtered data
     */
    const MODE_FORM = 'form';
    const MODE_DATA = 'data';
    const MODE_FILE = 'file';

    const DEFAULT_PARAMS = [
        'flags' => [
            'extractInfos' => true,
        ],
        'mode' => 'form',
        'size' => 50,
        'page' => 0,
        'sorts' => [],
        'filters' => [],
        'search' => null
    ];

    protected $mode = self::MODE_FORM;
    protected $backendSettings = [];
    protected $queryInfos = [];
    protected $queryParams = null;

    public function cacheKey(string $query): string
    {
        $md5 = md5($query);
        return "lazySearch_query_$md5";
    }

    public function getConfiguration(): array
    {
        return self::$configuration;
    }

    public function getQuerySettings(): array
    {
        return $this->backendSettings;
    }

    public function interpretMode(?string $mode)
    {
        $this->mode = match($mode){
            'json', 'data' => self::MODE_DATA,
            'file', 'export', 'csv' => self::MODE_FILE,
            default => self::MODE_FORM
        };
    }

    public function getClientExtras(): array
    {
        return $this->queryParams['extras'] ?? [];
    }

    public function initialize()
    {
        $req = Request::buildFromGlobals();

        $this->queryParams = $req->all();

        if ($toDecode = $req->params('params'))
            $this->queryParams = json_decode($toDecode, true, flags:JSON_THROW_ON_ERROR);

        $this->queryParams = array_merge(self::DEFAULT_PARAMS, $this->queryParams);
        $this->interpretMode($this->queryParams['mode']);
    }

    public function parseQueryFields(string $query, array $options)
    {
        $infos = &$this->queryInfos;

        if (self::$configuration['cached'])
        {
            if ($cachedInfo = Cache::getInstance()->try($this->cacheKey($query)))
                return $infos = $cachedInfo;
        }

        if (!preg_match('/^SELECT ?.+FROM/is', $query))
            throw new Exception('Invalid query format !');

        $sublessQuery = preg_replace_callback('/\(.+\)/', function($finding){
            return str_repeat('#', strlen($finding[0]));
        }, $query);

        $matches = [];
        preg_match('/^SELECT ?(?:\n|.)+? ?FROM/', $sublessQuery, $matches, PREG_OFFSET_CAPTURE);

        $fullExpression = $matches[0][0];
        $infos['select-range'] = [0, strlen($fullExpression)];
        $infos['fields-range'] = [7, strlen($fullExpression)-4];
        $fullExpression = substr($fullExpression, 7, -4);

        $fields = explode(',', $fullExpression);

        $i = 7;
        foreach ($fields as &$field)
        {
            $s = strlen($field);
            $field = substr($query, $i, $s);
            $i += $s+1;
        }

        $infos['fields'] = [];
        $processedFields = &$infos['fields'];
        $aliasesToObserve = [];
        foreach ($fields as $f)
        {
            $f = trim($f);
            $matches = [];

            if (preg_match('/(.*) as ([^.]+)$/', $f, $matches))
                list($_, $expr, $alias) = $matches;
            else if (preg_match('/(.*) ([^.]+)$/', $f, $matches))
                list($_, $expr, $alias) = $matches;
            else
                list($expr, $alias) = [$f, $f];

            if ($expr === $alias)
            {
                $matches = [];
                preg_match('/[^.]+$/', $alias, $matches);
                $alias = $matches[0];
            }

            // Support for '`field`'
            if (preg_match('/^[\'"`].+[\'"`]$/', $alias))
                $alias = substr($alias, 1, strlen($alias)-2);

            $toObserve = (!in_array($alias, $options['ignores']));

            $aliasIndex = array_push($processedFields, ['expression' => $expr, 'alias' => $alias, 'to-observe' => $toObserve])-1;
            if ($toObserve)
                $aliasesToObserve[] = [$alias, $aliasIndex];
        }

        if (self::$configuration['cached'])
            Cache::getInstance()->set($this->cacheKey($query), $infos, self::$configuration['cache_time_to_live']);

        return $infos;
    }

    public function wrapQuery(string $query)
    {
        $size = intval($this->queryParams['size']);
        $size = min(self::$configuration['size_limit'], $size);

        $page = intval($this->queryParams['page']);
        $offset = $size * $page;

        $sorts = [];
        $rawSorts = $this->queryParams['sorts'];

        if (count($rawSorts))
        {
            list($field, $mode) = $rawSorts;
            $sorts[] = "`$field` $mode";
        }

        if ((!count($sorts)) && preg_match('/ORDER BY ([^ ,]+( (ASC|DESC))?(, ?)?)+/', $query, $matches))
            $sorts[] = substr($matches[0], 8);

        $binds = [];

        $conditions = [];

        $filters = $this->queryParams['filters'];
        foreach ($filters as $field => $forbidden)
        {
            $forbidden = Utils::toArray($forbidden);

            if (!count($forbidden))
                continue;

            $nonNull = array_filter($forbidden, fn($e) => $e !== null);
            $nonNullCount = count($nonNull);

            /**
             * We cannot perform NOT/IN with null value, so we put 'valid' forbidden values
             * in one array and process any null value separately
             */
            if ($nonNullCount)
            {
                $nullExpr = (in_array(null, $forbidden)) ? '0' : '1';

                $conditions[] = '
                    IFNULL(`{}` NOT IN ('.join(', ', array_fill(0, $nonNullCount, '{}')). "), $nullExpr)
                ";
                array_push($binds, $field, ...$nonNull);
            }
            else if (in_array(null, $forbidden))
            {
                $conditions[] = '(`{}` IS NOT NULL)';
                array_push($binds, $field);
            }
        }

        if ($search = $this->queryParams['search'])
        {
            $search = join(
                '',
                array_map(fn($e) => "(?=.*$e)",
                    explode(' ', $search)
                )
            );

            $displayables = $this->queryInfos['fields'];
            $displayables = array_filter($displayables, fn($e)=> $e["to-observe"]);
            $displayables = array_map(fn($e) => "IFNULL(`".$e["alias"]."`, '')", $displayables);
            $displayables = join(", ' ', ", $displayables);

            $conditions[] = "CONCAT($displayables) RLIKE '{}'";
            array_push($binds, $search);
        }

        $limit = " LIMIT $size OFFSET $offset";
        array_push($binds, $size, $offset);

        $sqlFilters = Database::getInstance()->build(
                (count($conditions) ? 'WHERE ' . join(' AND ', $conditions) : '')
                .' '.(count($sorts) ? 'ORDER BY '.join(',', $sorts) : ''),
            $binds
        );

        $fields = array_map(fn($f) => '`' . $f['alias'] . '`', $this->queryInfos["fields"]);
        $fields = join(",", $fields);

        $sample = "FROM ($query) __ $sqlFilters";
        $wrappedQuery = "SELECT $fields FROM ($query) as __ $sqlFilters $limit";
        $countQuery = "SELECT COUNT(*) as c FROM ($query) as __ $sqlFilters";

        return [$wrappedQuery, $countQuery, $sample];
    }

    public function extractQueryInfos(string $sampleQuery, array $options)
    {
        $allowPossibilities = $options['extras']['allow-possibilities'] ?? self::$configuration['default_fetch_possibilities'];

        if (!$allowPossibilities)
            return;

        $aliasesToObserve = array_values(array_filter($this->queryInfos["fields"], fn($field) => $field["to-observe"]));
        $aliasesToObserve = array_map(fn($f) => $f['alias'], $aliasesToObserve);

        $aliasJump = [];
        foreach ($this->queryInfos["fields"] as &$field)
            $aliasJump[$field['alias']] = &$field;

        $counts = join(', ', array_map(fn($e)=>"COUNT(DISTINCT `$e`) as `$e`", $aliasesToObserve));
        $possibilitiesCount = array_values(Database::query("SELECT $counts FROM ($sampleQuery) as _")[0]);

        foreach ($possibilitiesCount as $i => $count)
        {
            $alias = $aliasesToObserve[$i];
            $thisField = &$aliasJump[$alias];
            $thisField['possibilities_number'] = $count;

            if ($count < 100)
            {
                $possibilities = Database::query("SELECT DISTINCT $alias FROM ($sampleQuery) as _", [], PDO::FETCH_BOTH);
                $possibilities = array_map(fn($e)=>$e[$alias], $possibilities);
                $thisField['possibilities'] = $possibilities;
            }
        }
    }

    public function makeList(
        string $query,
        array $options = [
            'links'=>[],
            'title' => 'Results',
            'views' => [],
            'scripts'=> [],
            'ignores' => [],
            'extras' => []
        ]
    ) {
        $this->backendSettings = $options;
        $options = &$this->backendSettings;

        $req = Request::buildFromGlobals();

        if ((self::$configuration['ignore_links']??true) == true)
            array_push($options['ignores'], ...array_map(fn($e)=>$e['value'], $options['links']));

        return match($this->mode){
            self::MODE_FORM => $this->getViewResponse($req, $options),
            self::MODE_DATA => $this->getDataResponse($query, $options),
            self::MODE_FILE => $this->getFileResponse($query, $options),
            default => Response::json('Unknown mode !')
        };
    }

    public function getViewResponse(Request $req, array $options)
    {
        $lazySearch = Renderer::getInstance()->render('LazySearch', ['url' => $req->getPath()]);
        if ($template = self::$configuration['template'] ?? false)
            return Renderer::getInstance()->render($template, ['lazySearch' => $lazySearch->getContent(), 'lazySearchOptions' => $options]);

        return $lazySearch;
    }

    public function getDataResponse(string $query, array $options)
    {
        $this->parseQueryFields($query, $options);
        list($wrappedQuery, $countQuery, $sample) = $this->wrapQuery($query);

        $resultCount = Database::getInstance()->query($countQuery)[0]['c'];
        if ($this->queryParams['flags']['extractInfos'] == true)
            $this->extractQueryInfos($query, $options);

        $response = [
            'options'      => $options,
            'meta'         => $this->queryInfos,
            'resultsCount' => $resultCount,
            'data'         => Database::getInstance()->query($wrappedQuery)
        ];

        return Response::json($response);
    }

    public function getFileResponse(string $query, array $options)
    {
        header("Content-Type: text/csv");

        $db = Database::getInstance();

        $this->parseQueryFields($query, $options);
        list($wrappedQuery, $countQuery, $sample) = $this->wrapQuery($query);
        $wrappedQuery = "SELECT * $sample";

        $resultCount = intval($db->query($countQuery)[0]['c']);

        $stream = fopen('php://output', 'w');

        $userAgent = Request::buildFromGlobals()->getHeaders()["User-Agent"] ?? "Window";
        $utf8Of = str_contains($userAgent, "Window") ?
            fn($data) => iconv( mb_detect_encoding( $data ), 'Windows-1252//TRANSLIT', $data ):
            fn($data) => $data;

        $writeCSV = fn($data) => fputcsv($stream, $data, ";");

        if ($resultCount)
        {
            $headers = array_keys($db->query($wrappedQuery.' LIMIT 1')[0]);
            $writeCSV($headers);
            flush();

            $pageSize = self::$configuration['export_chunk_size'];
            for($offset=0; $offset<$resultCount; $offset+=$pageSize)
            {
                $chunk = $db->query($wrappedQuery . " LIMIT $pageSize OFFSET $offset");
                foreach ($chunk as $row)
                {
                    foreach ($row as &$data)
                        $data = $utf8Of($data ?? '');
                    $writeCSV($row);
                }
                flush();
            }
        }

        fclose($stream);
        die;
    }

    public function makeOptions(
        array $links=[],
        string $title='Results',
        string|array $views=[],
        string|array $scripts=[],
        string|array $ignores=[],
        array $extras=[],
        array $defaultFilters=[],
        array $defaultSorts=[]
    ) {
        return [
            'title' => $title,
            'views' => is_array($views) ? $views: [$views],
            'scripts' => is_array($scripts) ? $scripts: [$scripts],
            'ignores' => is_array($ignores) ? $ignores: [$ignores],
            'links' => $links,
            'extras' => $extras,
            'defaultFilters' => $defaultFilters,
            'defaultSorts' => $defaultSorts,
        ];
    }

    public function makeLink(
        string $field,
        string $prefix,
        string $value
    ) {
        return [
            'field' => $field,
            'prefix' => $prefix,
            'value' => $value,
        ];
    }
}
