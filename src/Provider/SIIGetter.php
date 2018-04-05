<?php
namespace USD\API\Provider;

use USD\API\Definition\Getter;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Goutte\Client as GClient;

/**
 * Getter from http://www.sii.cl
 * @author Aldarien
 *
 */
class SIIGetter implements Getter
{
    /**
     * Getter name for identifying in configuration
     * @var string
     */
    protected $getter;
    /**
     * Client connection
     * @var GuzzleHttp\Client
     */
    protected $client;

    public function __construct()
    {
        $this->getter = 'sii';
        $this->client = new Client(['base_uri' => config('getters.' . $this->getter . '.url')]);
    }
    /**
     * Gets the crawler for the web page according to the configuration file
     * @param int $year
     * @return boolean|\Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawler(int $year)
    {
        $getter = 'getters.' . $this->getter;
        $url = str_replace('<year>', $year, config($getter . '.part'));
        try {
            $request = $this->client->request('GET', $url);
        } catch (ClientException $e) {
            return false;
        }

        if ($request->getStatusCode() != 200) {
            return false;
        }
        $client = new GClient();
        $uri = config($getter . '.url') . $url;
        $crawler = $client->request('GET', $uri);
        return $crawler;
    }
    /**
     *
     * {@inheritDoc}
     * @see \Money\Definition\Getter::get()
     */
    public function get(int $year)
    {
        $crawler = $this->getCrawler($year);
        if (!$crawler) {
            return false;
        }
        $nodes = $crawler->filter("#table_export td[style='text-align:right;']");

        $ufs = [];
        $tz = new \DateTimeZone(config('app.timezone'));
        $today = Carbon::today($tz);
        $n = -1;
				for ($d = 1; $d <= 31; $d ++) {
        		for ($m = 1; $m <= 12; $m ++) {
                $fecha = Carbon::createFromDate($year, $m, $d, $tz);
								if ($fecha->month != $m) {
                    continue;
                }
                $n ++;

                try {
                    $node = $nodes->eq($n);
										$pusd = $node->text();
										if (ord(mb_convert_encoding($pusd, 'UTF-8', 'ISO-8859-1')) == 195) {
                        continue;
                    }
                    $usd = (float) str_replace('$', '', str_replace(',', '.', str_replace('.', '', $pusd)));

                    $usds[$fecha->format('Y-m-d')] = $usd;
                } catch (\InvalidArgumentException $e) {
                }
            }
        }

        return $usds;
    }
}
