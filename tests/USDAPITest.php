<?php
use PHPUnit\Framework\TestCase;
use Goutte\Client;

class USDPITest extends TestCase
{
	protected $client;

	public function setUp()
	{
		$this->client = new Client();
		$this->client->setHeader('Accept', 'application/json');
	}

	protected function callApi($input)
	{
		$url = 'http://localhost/usd/api/?' . http_build_query($input);
		$this->client->request('GET', $url);
	}
	protected function assertIfStatusOK()
	{
		$status = $this->client->getResponse()->getStatus();
		$this->assertEquals($status, 200, 'HTTP status not OK.');
	}
	protected function getData()
	{
		$data = json_decode($this->client->getResponse()->getContent());
		return $data;
	}

	public function testGetUsdForDate()
	{
		$input = ['cmd' => 'value', 'date' => '2017-08-01'];
		$this->callApi($input);

		$this->assertIfStatusOk();

		$data = $this->getData();

		$output = (object) ['total' => 1, 'usd' => (object) ['value' => 652.23]];

		$this->assertEquals($output->total, $data->total, 'Different amount of values found.');
		$this->assertEquals(round($output->usd->value, 1), round($data->usd->value, 1), 'UF value is incorrect.');
	}
	public function testGetUsdsForYear()
	{
		$input = ['cmd' => 'list', 'year' => 2016];
		$this->callApi($input);

		$this->assertIfStatusOk();

		$data = $this->getData();

		$output = (object) ['total' => 251, 'usds' => [59 => (object) ['value' => 677.16]]];
		$i = 59;

		$this->assertEquals($output->total, $data->total, 'Different amount of values found.');
		$this->assertEquals(round($output->usds[$i]->value, 2), round($data->usds[$i]->value, 2));
	}
	public function testHelp()
	{
		$input = ['cmd' => 'help'];
		$this->callApi($input);

		$this->assertIfStatusOk();

		$data = $this->getData();

		$this->assertObjectHasAttribute('commands', $data);
	}
	public function testTransformToCLP()
	{
		$usd = 20;
		$input = ['cmd' => 'transform', 'value' => $usd, 'date' => '2017-08-01', 'to' => 'clp'];
		$this->callApi($input);

		$this->assertIfStatusOK();

		$data = $this->getData();

		$this->assertEquals($input['date'], $data->date);
		$result = round($usd * 652.23);
		$this->assertEquals($result, round($data->to));
	}
    public function testDeleteDay()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        $input = ['cmd' => 'delete', 'date' => $date];
        $this->callApi($input);

        $this->assertIfStatusOK();

        $data = $this->getData();
        $this->assertEquals('ok', $data->status);
        $this->assertEquals(1, $data->total);
    }
    public function testGetForDeletedDay()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        $input = ['cmd' => 'value', 'date' => $date];
        $this->callApi($input);

        $this->assertIfStatusOK();

        $data = $this->getData();
        $this->assertEquals(1, $data->total);
        $this->assertEquals($date, $data->usd->date);
    }
}
?>
