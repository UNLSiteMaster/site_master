
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
			//Now we need to return a result objec
			let links = [];

			//Loop though all elements on the page
			let all = document.getElementsByTagName("a");
			for (let i = 0; i < all.length; i++) {
				// Record the element name
				links.push(all[i].href);
			}

			return {
				//The results are stored in the 'results' property
				results: links,

				//The metric name is stored in the 'name' property with the same value used in Metric::getMachineName()
				'name': 'core-links'
			};
		}, options);
	};
};
