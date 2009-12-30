$(document).ready(function () {
	$('#add-button').click(function () {
		$('fieldset.add').show();
		return false;
	});
	$('fieldset.add')
		.hide()
		.children('button')
			.click(function () {
				$('fieldset.add input').attr('disabled', true);
				$.ajax({
					// Data
					"data": {
						"method": "add-row",
						"item": $('#id').val(),
						"name": $('#add-name').val(),
						"type": $('#add-type').val()
					},
					"dataType": "json",
					
					// Callbacks
					"error": function () {
						$('fieldset.add').append('<p class="error">Couldn\'t add a row, an error occurred. <a href="#close">Close</a>.</p>');
						$('fieldset.add input').attr('disabled', false);
					},
					"success": function (data) {
						//window.location.reload();
						var row = $('<div class="form-row"></div>').html(data.row);
						$('fieldset.main').append(row);
						$('fieldset.add input').attr('disabled', false);
						$('fieldset.add').hide();
					},
					
					// Request
					"type": "POST",
					"url": "api"
				});
				return false;
			});
	$('a[href="#close"]').live('click', function () {
		$(this).parent().remove();
	});
	
	$('fieldset.main .form-row').each(function (i) {
		if ( ! $(this).hasClass('no-delete') )
			$(this).append('<p class="actions"><span class="remove-row">Remove</span></p>');
	});
	
	$('.remove-row').live('click', function () {
		var name = $(this).parents('.form-row').children('label').attr('for');
		var e = this;
		$.ajax({
			// Data
			"data": {
				"method": "delete-row",
				"item": $('#id').val(),
				"name": name,
			},
			"dataType": "json",
			
			// Callbacks
			"error": function () {
				$(e).append('<p class="error">Couldn\'t remove row, an error occurred. <a href="#close">Close</a>.</p>');
			},
			"success": function () {
				$(e).parents('.form-row').remove();
			},
			
			// Request
			"type": "POST",
			"url": "api"
		});
	});

	$('.deletelink').click(function () {
		if ( !confirm('Are you sure you want to delete this item?'))
			return false;
		if ( !confirm('Really really sure? You can\'t undo this.'))
			return false;
		
		$.ajax({
			// Data
			"data": {
				"method": "delete-item",
				"item": $('#id').val()
			},
			"dataType": "json",
			
			// Callbacks
			"error": function () {
				$(".submit-row").append('<p class="error">Couldn\'t remove row, an error occurred. <a href="#close">Close</a>.</p>');
			},
			"success": function () {
				window.location.href = './';
			},
			
			// Request
			"type": "POST",
			"url": "api"
		})
	});
});