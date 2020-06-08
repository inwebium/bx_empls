function changePageSize()
{
	var selectedSize = event.target.value;
	var urlParams = new URLSearchParams(window.location.search);
	var navigationParam = urlParams.get('employees-navigation');

	// Если в get есть параметр для пагинации
	if (navigationParam !== null) {
		// Если выбрано "Все"
		if (selectedSize == 0) {
			urlParams.set('employees-navigation', 'page-all');
		} else {
			urlParams.set('employees-navigation', 'page-1-size-' + selectedSize);
		}
	} else {
		// Если выбрано "Все"
		if (selectedSize == 0) {
			urlParams.append('employees-navigation', 'page-all');
		} else {
			urlParams.append('employees-navigation', 'page-1-size-' + selectedSize);
		}
		
	}
	
	document.location.search = urlParams;
}
