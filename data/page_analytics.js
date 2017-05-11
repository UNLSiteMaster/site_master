
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
			var results = {
				element: {},
				class: {},
				attribute: {}
			};
			
			function bumpResult(type, key, value) {
				if (typeof results[type][key] === 'undefined') {
					results[type][key] = {};
				}
				
				
				if (typeof results[type][key][value] === 'undefined') {
					results[type][key][value] = 0;
				}

				results[type][key][value]++;
			}

			//Loop though all elements on the page
			var all = document.getElementsByTagName("*");
			for (var i = 0; i < all.length; i++) {
				// Record the element name
				bumpResult('element', all[i].nodeName, null);
				
				// Record classes
				var classes = all[i].getAttribute('class');
				
				if (null !== classes) {
					classes = classes.split(' ');
					for (var ii in classes) {
						bumpResult('class', classes[ii], null);
					}
				}

				//The following generates a lot of data. Lets pass on it until we know we really need it.
				//var attrs = all[i].attributes;
				//for(var ii = attrs.length - 1; ii >= 0; ii--) {
				//	bumpResult('attribute', attrs[ii].name, attrs[ii].value);
				//}
			}
			
			return {
				//The results are stored in the 'results' property
				results: results,

				//The metric name is stored in the 'name' property with the same value used in Metric::getMachineName()
				'name': 'core-page-analytics'
			};
		}, options);
	};
};
