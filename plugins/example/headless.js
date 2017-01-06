
/**
 * Must define an evaluate function that is compatible with nightmare.use()
 * 
 * This essentially defines a nightmare.js plugin which should run the tests and return a result object (see code for an example of the result object)
 * 
 * @param metric_name the metric's machine name will be passed so that the results object can set the name correctly
 * @returns {Function}
 */
exports.evaluate = function(options) {
    //using the given nightmare instance
    return function(nightmare) {
        nightmare.evaluate(function(options) {
            //Now we need to return a result object
            return {
                //The results are stored in the 'results' property
                'results': {
                    'page_title': document.title
                },
                
                //The metric name is stored in the 'name' property with the same value used in Metric::getMachineName()
                'name': 'example'
            };
        }, options);
    };
};
