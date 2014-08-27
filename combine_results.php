<?PHP

ini_set('memory_limit', '1024M');

$results = new stdClass();
$results -> results = array();

for ($i = 1; $i < $_SERVER['argc']; $i++) 
{
  $arg = $_SERVER['argv'][$i];

  // Load the results
  fprintf(STDERR, "Loading $arg\n");
  $testResults = json_decode(file_get_contents($arg));
  if (false === $testResults) {
    fprintf(STDERR, "Could not load results for '%s'\n", $arg);
    continue;
  }
  if (!isset($testResults -> results)) {
    fprintf(STDERR, "No results found in '%s'\n", $arg);
    continue;
  }
  
  $results->results = array_merge($results->results, $testResults->results);
//  $results->results += $testResults->results;
}

print(json_encode($results, JSON_PRETTY_PRINT));

?>

