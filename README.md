# HTML5 test result tools #

## combine_results.php ##

When executing the tests it may be convenient to run only a single directory at a time. This script allows
multiple JSON result files to be combined in to a single file for use by the other tools.

	combine_results.php <file.json> ...

## generate_reference_results.php ##

This tool takes the results from multiple browser runs and creates a merged version of them based on a specified threshold.

	generate_reference_results.php [--min-pass-rate <int>] <file.json> ...

## validate.php ##

Here is the test run with the two supplied test files

	eric@eric-T1650:~/workspace/dlna$ php validate.php good.json test.json 
	Loading expected results from: good.json
	Loading test results from: test.json
	Verifying the test results
	
	FAILURE - SUBTEST NOT RUN
	  Test Name: /webgl/compressedTexImage2D.html
	  Subtest Name: compressedTexImage2D
	
	FAILURE - SUBTEST FAILED
	  Test Name: /webgl/compressedTexSubImage2D.html
	  Subtest Name: compressedTexSubImage2D
	
	FAILURE - TEST NOT RUN
	  Test Name: /webgl/texImage2D.html
	
	FAILURE - SUBTEST FAILED
	  Test Name: /webgl/uniformMatrixNfv.html
	  Subtest Name: Should not throw for 3
	
	FAILURE - SUBTEST FAILED
	  Test Name: /webgl/uniformMatrixNfv.html
	  Subtest Name: Should not throw for 4
	
	Test results:
	  Tests not run: 1
	  Subtests not run: 1
	  Tests failed: 2
	  Subtests failed: 3

