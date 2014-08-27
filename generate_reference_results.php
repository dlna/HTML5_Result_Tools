<?PHP

ini_set('memory_limit', '1024M');

$results = new stdClass();
$results->auto = array();
$results->reference = array();

$allStatus = array('PASS', 'FAIL', 'TIMEOUT', /* 'ERROR',*/ 'NOTRUN');
$allBrowserIds = array();

$minPassRate = 2;

for ($i = 1; $i < $_SERVER['argc']; $i++) 
{
  $arg = $_SERVER['argv'][$i];
  
  if('--min-pass-rate' == $arg)
  {
    $minPassRate = $_SERVER['argv'][++$i];
    continue;
  }

  // Load the results
  fprintf(STDERR, "Loading %s\n", $arg);
  $testResults = json_decode(file_get_contents($arg));
  if (false === $testResults) {
    fprintf(STDERR, "Could not load results for '%s'\n", $arg);
    continue;
  }
  if (!isset($testResults -> results)) {
    fprintf(STDERR, "No results found in '%s'\n", $arg);
    continue;
  }

  // Get stats per sub-test
  foreach ($testResults->results as $test) 
  {
    if(is_array($test->test))
    {
      $testName = $test->test[0];
      if(!array_key_exists($testName, $results->reference)) 
      {
        $obj = new stdClass();
        $obj->name = $test->test;
        $obj->passCount = 0;
        $results->reference[$testName] = $obj;
      }

      if('PASS' == $test->status) {
        $results->reference[$testName]->passCount++;
      }
    }
    else
    {
      $testName = $test->test;
      if(!array_key_exists($testName, $results->auto)) {
        $results->auto[$testName] = array();
      }
  
      foreach ($test->subtests as $subtest) 
      {
        $subtestName = str_replace(array('web-platform.test:8000', 'WEB-PLATFORM.TEST:8000'), 
                                   array('w3c-test.org', 'W3C-TEST.ORG'), 
                                   $subtest->name);
        if(!array_key_exists($subtestName, $results->auto[$testName])) {
          $results->auto[$testName][$subtestName] = 0;
        }

        // Any test with the something other than a PASS or FAIL we can not be 
        // certain of which sub test caused a problem so we fail them all
        if(('OK' == $test->status || 'PASS' == $test->status) && 'PASS' == $subtest->status) {
          $results->auto[$testName][$subtestName]++;
        }
      }
    }
  }
}

$combined = new stdClass();
$combined->results = array();

ksort($results->auto);
foreach ($results->auto as $test => $subtests) 
{
  $testObj = new stdClass();
  $testObj->test = $test;
  $testObj->status = 'OK';
  $testObj->subtests = array();
  
  ksort($subtests);
  foreach ($subtests as $subtest => $result)
  {
    $subtestObj = new stdClass();
    $subtestObj->name = $subtest;
    $subtestObj->status = ($result >= $minPassRate) ? 'PASS' : 'FAIL';
    
    $testObj->subtests[] = $subtestObj;
  }
  
  $combined->results[] = $testObj;
}

ksort($results->reference);
foreach ($results->reference as $test => $result) 
{
  $testObj = new stdClass();
  $testObj->test = $result->name;
  $testObj->subtests = array();
  $testObj->status = ($result->passCount >= $minPassRate) ? 'PASS' : 'FAIL';
  
  $combined->results[] = $testObj;
}

print(json_encode($combined, JSON_PRETTY_PRINT));

?>

