<?php declare(strict_types=1);

namespace h4kuna\CriticalCache\Tests\Integration;

require __DIR__ . '/../../../vendor/autoload.php';

use h4kuna\CriticalCache\Services\UniqueValueServiceAbstract;
use h4kuna\CriticalCache\Services\RandomGenerator;
use h4kuna\CriticalCache\Services\UniqueValuesGeneratorService;
use PDO;
use PDOException;

$database = __DIR__ . '/test.db';
@unlink($database);

// Připojení k databázi SQLite
try {
	$pdo = new PDO('sqlite:' . $database);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Vytvoření tabulky, pokud neexistuje
	$sql = "CREATE TABLE IF NOT EXISTS test (
                unique_value TEXT NOT NULL
            )";
	$pdo->exec($sql);

	$indexSql = "CREATE INDEX IF NOT EXISTS idx_unique_value ON test (unique_value)";
	$pdo->exec($indexSql);

	echo "Tabulka a index byly vytvořeny nebo již existují.<br>";
} catch (PDOException $e) {
	echo "Chyba připojení: " . $e->getMessage();
}

function placeholder(array $data, string $str): string
{
	return substr(str_repeat($str, count($data)), 0, -2);
}


$checker = new class($pdo) extends UniqueValueServiceAbstract {
	public function __construct(private readonly PDO $pdo)
	{
		parent::__construct(new RandomGenerator(),200000);
	}

	public function check(array $data): iterable
	{
		$sql = sprintf('SELECT unique_value FROM test WHERE unique_value IN (%s)', placeholder($data, '?, '));
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array_values($data));
		foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row) {
			yield $row->unique_value;
		}
	}
};

$uniqueGenerator = new UniqueValuesGeneratorService();

$limit = floor(500_000 / $checker->getQueueSize());
$start = microtime(true);
for ($i = 0; $i < $limit; ++$i) {
	$values = $uniqueGenerator->execute($checker);

	$sql = "INSERT INTO test (unique_value) VALUES " . placeholder($values, '(?), ');

	$stmt = $pdo->prepare($sql);
	$stmt->execute($values);
}

echo microtime(true) - $start;
