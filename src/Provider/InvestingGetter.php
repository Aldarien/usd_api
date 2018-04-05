<?php
namespace USD\API\Provider;

use USD\API\Definition\Getter;

class InvestingGetter implements Getter
{
    protected $getter;
    protected $url;
    protected $params;
    protected $vars;

    public function __construct()
    {
        $this->getter = 'investing';
        $this->url = config('getters.' . $this->getter . '.url');
        $this->params = config('getters.' . $this->getter . '.params');
        $this->vars = array_combine(config('getters.' . $this->getter . '.variables'), [0, 0]);
    }
    public function get(int $year)
    {
        $this->vars['st_date'] = '01/01/' . $year;
        $this->vars['end_date'] = '31/12/' . $year;

        $query = array_merge($this->params, $this->vars);

        $options = [
            "http" => [
                "header" => "Accept: text/plain, */*; q=0.01" . PHP_EOL .
                    "Accept-Encoding: gzip, deflate, br" . PHP_EOL .
                    "Accept-Language: en-GB,en;q=0.5" . PHP_EOL .
                    "Connection: keep-alive" . PHP_EOL .
                    "Content-Length: 181" . PHP_EOL .
                    "Content-Type: application/x-www-form-urlencoded" . PHP_EOL .
                    "Cookie: PHPSESSID=62btcuusch0mu16e5fp9…mizelyPendingLogEvents=%5B%5D" . PHP_EOL .
                    "Host: es.investing.com" . PHP_EOL .
                    "Referer: https://es.investing.com/currencies/usd-clp-historical-data" . PHP_EOL .
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; …) Gecko/20100101 Firefox/59.0" . PHP_EOL .
                    "X-Requested-With: XMLHttpRequest",
                "method" => 'POST',
                "content" => http_build_query($query)
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($this->url, null, $context);
        d($result);
    }
}
?>
