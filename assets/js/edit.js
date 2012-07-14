var backupInputData = {};	//storage for on-fly backuping of form values; used in NULL checkboxes mechanism 
var snapshotInputData = {}; //starter form data to collate onsubmit and mark changed values

function focusNearNullF()
{
	var $nullF = $(this).parent(0).find('input[type=checkbox].nullf');
	window.setTimeout(function() {
			if(!$nullF.attr('checked'))
				$nullF.attr('checked', true);
		}, 410);
}

function clearNearNullF()
{
	var $this = $(this);
	backupInputData[$this.attr('name')] = $this.val();
	$this.val('');
}

function restoreNearNullF()
{
	var $this = $(this);
	if(!backupInputData[$this.attr('name')])
		return;
	$this.val(backupInputData[$this.attr('name')]);
}

function nullFieldCheckBox()
{
	var $this = $(this);
	var $input = $this.parent().find('input[type!=button]:not(input[type=checkbox].nullf),textarea');
	if(this.checked)
		$input.each(restoreNearNullF);
	else
		$input.each(clearNearNullF);
}

function numTipChange()
{
	var $this = $(this);
	$this.parent(0).parent(0).find('input[type=text]').val($(this).val());
}

function foreignKeyQuery(request, response)
{
	var params = {term: request.term, 'fieldBy': $(this.element).parent(0).find('select').val()};
	if(this.options.extraParams)
	{
		for(var param in this.options.extraParams)
			params[param] = this.options.extraParams[param];
	}
	$.ajax(this.options.sourceUrl, {
			type: 'post',
			dataType: 'json',
			data: params,
			success: response
		});
}

function foreignKeySelected(event, ui)
{
	var $block = $(this).parents('div.item');
	$block.find('select').not('[id^=foreignSearchBy]').html('<option value="'+ ui.item.value +'">'+ ui.item.label +'</option>');
	$block.find('input[type=hidden]').val(ui.item.value);
	$block.find('input[type=checkbox].nullf').attr('checked', true);
	return false;
}

function filteredInputs($form)
{
	return $form.find('input,textarea,select').filter('[name^="AA["]:not([name^="AA[AAnullf]"])');
}

function snapshotInputValue()
{
	var $this = $(this);
	snapshotInputData[$this.attr('name')] = ($this.attr('type')=='checkbox' ? $this.attr('checked') : $this.val());
}

function markChangedField()
{
	var $this = $(this);
	if(snapshotInputData[$this.attr('name')] != ($this.attr('type')=='checkbox' ? $this.attr('checked') : $this.val()))
		$(this.form).append('<input type="hidden" name="isChanged'+ $this.attr('name') +'" value="1"/>');
}

function checkboxNewPassword()
{
	var $this = $(this);
	var $input = $this.parents('.block_password').find('input[type=password][name$="[val]"]');
	$input.attr('disabled', !$this.attr('checked'));
}

function tabKeyHandler(e)
{
	if(e.keyCode == 9)
	{
		this.value += "\t";
		if(e.preventDefault)
			e.preventDefault();
		return false;
	}
}

$(document).ready(function(){
	var $form = $('#editform');
	filteredInputs($form).each(snapshotInputValue);
	$form.find('.nullf input[type=checkbox].nullf').change(nullFieldCheckBox).attr('title', 'Disable the checkbox If you`re going to leave the field as empty');	//Снимите отметку, чтобы оставить поле пустым (как NULL)
	$form.find('.nullf select:not(:has(option))').parent(0).find('input[type=checkbox].nullf').attr('checked', false).attr('disabled', true);
	$form.find('.nullf').find('input,textarea').filter(':not(input[type=checkbox].nullf)').focus(focusNearNullF);
	$form.find('.block_password input[type=checkbox][name$="[is_new]"]').change(checkboxNewPassword);

	$form.find('.time-panel').each(function() {
		var	$timePanel = $(this);
		var datePickerOpts = {
				onSelect: function(dateText, inst) {
					$timePanel.find('select[name$="[d]"]').val(parseInt(dateText.substr(3, 2)));
					$timePanel.find('select[name$="[m]"]').val(parseInt(dateText.substr(0, 2)));
					$timePanel.find('input[name$="[y]"]').val(dateText.substr(6, 4));
					return false;
				},
				changeYear: true
			};
		//If min or max validators were set
		if($form.find('.time-panel .calendar span.mindate'))
			datePickerOpts.minDate = new Date($form.find('.time-panel .calendar span.mindate').html()*1000);
		if($form.find('.time-panel .calendar span.maxdate'))
			datePickerOpts.maxDate = new Date($form.find('.time-panel .calendar span.maxdate').html()*1000);

		$timePanel.find('.calendar input').datepicker(datePickerOpts);
	});

	$form.find('.block_tinytext textarea, .block_text textarea').each(function() {
		if(this.addEventListener)
			this.addEventListener('keydown', tabKeyHandler, false);
		else if(this.attachEvent)
			this.attachEvent('onkeydown', tabKeyHandler);
	})

	$form.submit(function() {
		filteredInputs($form).each(markChangedField);
		return true;
	});
});