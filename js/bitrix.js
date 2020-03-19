// our application constructor
function application () {
}

application.prototype.AllFields = function () {

	BX24.callMethod(
		"crm.deal.list", 
		{ }, 
		function(result) 
		{
			if (result.error()) {
				displayErrorMessage('К сожалению, произошла ошибка получения сделок. Попробуйте повторить отчет позже');
				console.error(result.error());
			}
			else
			{
				let data = result.data();
				let select = document.createElement('select');
				select.id = 'example';
				select.multiple = 'multiple';
				select.name = 'name';
				for (let indexField in data) {
				    if ((indexField != 'ID') || (indexField != 'TITLE')) {
    				    let option = document.createElement('option');
    				    option.value = indexField;
    				    if (indexField.includes('UF_CRM')) {
    				        let optionText = document.createTextNode(data[indexField].formLabel);
    				        option.appendChild(optionText);
    				    }
    				    else {
    				        let optionText = document.createTextNode(indexField);
    				        option.appendChild(optionText);
    				    }
    				 select.appendChild(option);
    				}
				if (result.more())
					result.next();
				else {
					$('#fields').html(select);					
				}
				}	
			}
		}
	);
}


// create our application
app = new application();