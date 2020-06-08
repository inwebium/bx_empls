function changePageSize()
{
	var selectedSize = event.target.value;
	var urlParams = new URLSearchParams(window.location.search);
	var navigationParam = urlParams.get('employees-navigation');

	if (navigationParam !== null) {
		var splittedNavParams = navigationParam.split('-');
		var sizeKeyIndex = splittedNavParams.indexOf('size');
		var joinedParams = '';

		if (selectedSize == 0) {
			var pageKeyIndex = splittedNavParams.indexOf('page');

			if (pageKeyIndex >= 0) {
				splittedNavParams[pageKeyIndex + 1] = 'all';
			} else {
				splittedNavParams.push('page');
				splittedNavParams.push('all');
			}

			joinedParams = splittedNavParams.join('-');
		} else {
			if (sizeKeyIndex >= 0) {
				splittedNavParams[sizeKeyIndex + 1] = selectedSize;
			} else {
				splittedNavParams.push('size');
				splittedNavParams.push(selectedSize);
			}

			joinedParams = splittedNavParams.join('-').replace('page-all', 'page-1');
		}



		urlParams.set('employees-navigation', joinedParams);
	} else {
		if (selectedSize == 0) {
			urlParams.append('employees-navigation', 'page-all');
		} else {
			urlParams.append('employees-navigation', 'size-' + selectedSize);
		}
		
	}

	document.location.search = urlParams;
}
