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
	$.getJSON(this.options.sourceUrl, params, response);
}

function foreignKeySelected(event, ui)
{
	$(this).parent(0).prev('select').html('<option value="'+ ui.item.value +'">'+ ui.item.label +'</option>');
	$(this).parent(0).parent(0).find('input[type=checkbox].nullf').attr('checked', true);
	return false;
}

function tuneNumTip()
{
	var $this = $(this);
	if($this.parent(0).parent(0).find('label').html().match(/год/i))
	{
		var d = new Date();
		$this.html('');
		for(var y=1960; y<d.getFullYear(); y++)
		{
			$this.append('<option value="'+ y +'">'+ y +'</option>');
		}
		$this.find('option:last').attr('selected', true);
	}
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

$(document).ready(function(){
	var $form = $('#editform');
	filteredInputs($form).each(snapshotInputValue);
	$form.find('.nullf input[type=checkbox].nullf').change(nullFieldCheckBox).attr('title', 'Disable the checkbox If you`re going to leave the field as empty');	//Снимите отметку, чтобы оставить поле пустым (как NULL)
	$form.find('.nullf select:not(:has(option))').parent(0).find('input[type=checkbox].nullf').attr('checked', false).attr('disabled', true);
	$form.find('.nullf').find('input,textarea').filter(':not(input[type=checkbox].nullf)').focus(focusNearNullF);
	$form.find('.block_password input[type=checkbox][name$="[is_new]"]').change(checkboxNewPassword);
	$form.find('.num-tip select').each(tuneNumTip).change(numTipChange);
	//$('#editform input,textarea').filter('[name^="AA["]').focus(function() {$(this).addClass('focused')}).blur(function() {$(this).removeClass('focused')});

	$form.submit(function() {
		filteredInputs($form).each(markChangedField);
		return true;
	});
});