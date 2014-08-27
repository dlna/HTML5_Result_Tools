<?PHP 

ini_set('memory_limit', '1024M');

// Check the calling parameters
if (3 != $_SERVER['argc']) {
  fprintf(STDOUT, "Usage: php validate.php <good results> <test results>\n");
  exit();
}

// Load the results file
function load($file)
{
  $testResults = json_decode(file_get_contents($file));
  if (false === $testResults) {
    fprintf(STDERR, "Could not load results for '%s'\n", $file);
    exit("Exiting");
  }
  if (!isset($testResults -> results)) {
    fprintf(STDERR, "No results found in '%s'\n", $file);
    exit("Exiting");
  }

  return $testResults;
}

fprintf(STDOUT, "Loading expected results from: %s\n", $_SERVER['argv'][1]);
$goodResults = load($_SERVER['argv'][1]);

fprintf(STDOUT, "Loading test results from: %s\n", $_SERVER['argv'][2]);
$testResults = load($_SERVER['argv'][2]);

// Index the testResults so we can look them up
$testResults->tests = array();
foreach ($testResults->results as $test)  {

  $testName = is_array($test->test) ? $test->test[0] : $test->test;
  if(!array_key_exists($testName, $testResults->tests)) {
    $testResults->tests[$testName] = array();
  }

  foreach ($test->subtests as $subtest) {
    $subtestName = str_replace(array('web-platform.test:8000',
				     'WEB-PLATFORM.TEST:8000'), 
			       array('w3c-test.org',
				     'W3C-TEST.ORG'), 
			       $subtest->name);

    // Update the results
    $testResults->tests[$testName][$subtestName] = $subtest->status;
  }
}

fprintf(STDOUT, "Verifying the test results\n\n");

// Test statistics
$testsNotRun = 0;
$subtestsNotRun = 0;
$testsFailed = 0;
$subtestsFailed = 0;

// Work through each test
foreach ($goodResults->results as $test)  {

  $testName = is_array($test->test) ? $test->test[0] : $test->test;

  if(!array_key_exists($testName, $testResults->tests)) {

    fprintf(STDOUT, "FAILURE - TEST NOT RUN\n");
    fprintf(STDOUT, "  Test Name: %s\n\n", $testName);

    $testsNotRun++;
    continue;
  }

  // Counter to see if any of the subtests fail
  $failCheck = $subtestsFailed;

  foreach ($test->subtests as $subtest) {
    $subtestName = str_replace(array('web-platform.test:8000',
				     'WEB-PLATFORM.TEST:8000'), 
			       array('w3c-test.org',
				     'W3C-TEST.ORG'), 
			       $subtest->name);

    // Check the passed tests
    if ($subtest->status === "PASS") {

      if(!array_key_exists($subtestName, $testResults->tests[$testName])) {
	  
	fprintf(STDOUT, "FAILURE - SUBTEST NOT RUN\n");
	fprintf(STDOUT, "  Test Name: %s\n", $testName);
	fprintf(STDOUT, "  Subtest Name: %s\n\n", $subtestName);

	$subtestsNotRun++;
	continue;
      }


      if ($testResults->tests[$testName][$subtestName] === "PASS") {

	//fprintf(STDOUT, "  PASS: %s /  %s\n", $testName, $subtestName);

      } else {

	fprintf(STDOUT, "FAILURE - SUBTEST FAILED\n");
	fprintf(STDOUT, "  Test Name: %s\n", $testName);
	fprintf(STDOUT, "  Subtest Name: %s\n\n", $subtestName);

	$subtestsFailed++;
      }
    }
  }

  // Check if any of the subtests failed
  if ($failCheck != $subtestsFailed) {
    $testsFailed++;
  }
}

// Print out the test results
fprintf(STDOUT, "Test results:\n");
if ($testsNotRun) {
  fprintf(STDOUT, "  Tests not run: %s\n", $testsNotRun);
}
if ($subtestsNotRun) {
  fprintf(STDOUT, "  Subtests not run: %s\n", $subtestsNotRun);
}
if ($testsFailed) {
  fprintf(STDOUT, "  Tests failed: %s\n", $testsFailed);
}
if ($subtestsFailed) {
  fprintf(STDOUT, "  Subtests failed: %s\n", $subtestsFailed);
}
if (($testsNotRun + $subtestsNotRun + $testsFailed + $subtestsFailed) == 0) {
  fprintf(STDOUT, "  All tests passed\n");  
}

?>

