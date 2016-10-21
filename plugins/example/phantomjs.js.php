//Set properties in the metric_results object.

//1: Example of how to run a synchronous test
metric_results.page_title = page.evaluate(function() {
    return document.title;
});

//2. Async example
async_metrics.push('example'); //Tell sitemaster that it needs to wait for an async test (push the machine name of the metric)
page.evaluateAsync(function() {
    var phantomResults = {}; //Create a phantom results object
    phantomResults.metric = 'example'; //Set the metric name to the metric's machine name (must be the same as what was pushed to the async_metrics array)
    phantomResults.results = {}; //Create a results object
    phantomResults.results.async_page_title = document.title; //Set the data
    window.callPhantom(phantomResults); //pass back to sitemaster
});
