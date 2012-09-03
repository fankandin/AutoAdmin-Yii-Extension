var admwnd = null;

function openPopupWindow()
{
	if(admwnd && !admwnd.closed)
		admwnd.close();
	var width = 800;
	var height = 600;
	var screenX = window.pageXOffset + Math.floor((window.outerWidth - width)/2);
	var screenY = window.pageXOffset + Math.floor((window.outerHeight - height)/2);
	admwnd = window.open(this.href, "popup", "toolbar=0,location=0,directories=0,status=yes,menubar=0,scrollbars=yes,resizable=yes,copyhistory=0,width="+width+",height="+height+",screenX="+screenX+", screenY="+screenY);
	admwnd.focus();
}

var i =0;
function enlightRow()
{
	var $this = $(this);
	if($this.hasClass('enlighted'))
	{
		$this.removeClass('enlighted');
		return;
	}
	$this.parent(0).find('tr').removeClass('enlighted');
	$this.addClass('enlighted');
}

$(document).ready(function(){
	$('#data-list td.control .popup').click(openPopupWindow);
	$('#data-list tr').click(enlightRow);
	$('#search-panel input[type=reset]').click(function() {
		var $form = $(this.form);
		$form.find('[name=searchBy], [name=searchQ]').attr('disabled', true);
		$form.submit();
	});
});