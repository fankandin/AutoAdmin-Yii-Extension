var aaTextEditorButtonsAccessKeys = {
	'STRONG': 'B',
	'EM': 'I',
	'H3': '3',
	'H4': '4',
	'UL': 'L',
	'OL': 'O',
	'Link': 'W',
	'Mailto': 'M',
	'Img': 'P'
};

var aaHelp = {
	'text_enter_url': 'Input full URL-address',	//Введите полный адрес (URL)
	'text_enter_url_name': 'Input link`s text', //Введите название странички
	'text_enter_email': 'Enter the email address', //Введите email адрес
	'text_enter_ulist': 'Enter the other list item', //Введите пункт списка
	'error_no_url': 'You must enter URL', //Вы должны указать адрес (URL)
	'error_no_title': 'You must enter link`s text', //Вы должны указать название странички
	'error_no_email': 'You must enter any email address', //Вы должны ввести email адрес

	'strong_help': 'Bold text (alt+B)', //Полужирный текст (alt+B)
	'em_help': 'Italic text (alt+I)', //Курсивный текст (alt+I)',
	'h3_help': 'Header level 3 (alt+H)', //Заголовок 3-го уровня (alt+H)',
	'h4_help': 'Header level 4 (alt+J)', //Заголовок 4-го уровня (alt+J)',
	'ul_help': 'Unordered list (alt+L)', //Ненумерованный список (alt+L)',
	'ol_help': 'Ordered list (alt+O)', //Нумерованный список (alt+O)',
	'url_help': 'Insert a hyperlink (alt+W)', //Вставить гиперссылку (alt+W)',
	'email_help': 'Insert a mailto hyperlink (alt+E)', //Вставить гиперссылку на E-mail (alt+E)',
	'img_help': 'Insert an image or a flash movie (alt+P)', //Вставить изображение или Flash-ролик (alt+P)'
};
var aaOptions = {
	'clientPC': navigator.userAgent.toLowerCase(),
	'clientVer': parseInt(navigator.appVersion)
};
aaOptions['is_ie'] = ((aaOptions['clientPC'].indexOf("msie") != -1) && (aaOptions['clientPC'].indexOf("opera") == -1));
aaOptions['is_nav'] = ((aaOptions['clientPC'].indexOf('mozilla')!=-1) && (aaOptions['clientPC'].indexOf('spoofer')==-1)
                && (aaOptions['clientPC'].indexOf('compatible') == -1) && (aaOptions['clientPC'].indexOf('opera')==-1)
                && (aaOptions['clientPC'].indexOf('webtv')==-1) && (aaOptions['clientPC'].indexOf('hotjava')==-1));
aaOptions['is_moz'] = 0;
aaOptions['is_win'] = ((aaOptions['clientPC'].indexOf("win")!=-1) || (aaOptions['clientPC'].indexOf("16bit") != -1));
aaOptions['is_mac'] = (aaOptions['clientPC'].indexOf("mac")!=-1);

$(document).ready(function(){
	var $form = $('#editform');
	$form.find('.item.block_text input[type=button]').each(aaPrepareTextEditorButton);
	$form.find('.item.block_text textarea').click(aaStoreCaret).select(aaStoreCaret).keyup(aaStoreCaret);
	$form.find('.item.block_text textarea').each(function() {
		if(this.addEventListener)
			this.addEventListener('keydown', tabKeyHandler, false);
		else if(this.attachEvent)
			this.attachEvent('onkeydown', tabKeyHandler);
	})
});

function aaPrepareTextEditorButton()
{
	var $this = $(this);
	$this.click(aaTextEditorButtonClick);
}

function aaTextEditorButtonClick()
{
	var $textEditor = $(this).parent('.item').find('textarea');
	var value = $(this).val().toLowerCase();
	switch(value)
	{
		case 'img':
			aaImgUploadWindow($textEditor);
			break;
		case 'link':
			aaInsertUrl($textEditor);
			break;
		case 'mailto':
			aaInsertEmail($textEditor);
			break;
		case 'ul':
			aaInsertList($textEditor, 'ul');
			break;
		case 'ol':
			aaInsertList($textEditor, 'ol');
			break;
		case '<..>':
			value = window.prompt('Enter any tag (without <>):', '');	//Введите любой тег (без <>)
			if(value)
			{
				aaInsertTag($textEditor, value);
			}
			break;
		default:
			aaInsertTag($textEditor, value);
			break;
	}
}

function aaInsert(text, $textEditor)
{
	$textEditor.focus();
	if(document.selection)
		document.selection.createRange().text = text;
	else if($textEditor.get(0).selectionEnd)
		aaMozWrap($textEditor, '', text);
	else
		$textEditor.val($textEditor.val() + text);
}

function aaInsertUrl($textEditor)
{
	var FoundErrors = '';
	var enterURL = window.prompt(aaHelp['text_enter_url'], 'http://');
	if(!enterURL)
		return;
	$textEditor.focus();
	if ((aaOptions['clientVer'] >= 4) && aaOptions['is_ie'] && aaOptions['is_win']) {
		var range = document.selection.createRange();
		if(!range.text) {
			var enterTITLE = window.prompt(aaHelp['text_enter_url_name'], 'Link title');
			if(!enterTITLE)
				FoundErrors += "\n" + aaHelp['error_no_title'];
		}
		else
			enterTITLE = range.text;
	}
	else if($textEditor.attr('selectionEnd') && ($textEditor.attr('selectionEnd') - $textEditor.attr('selectionStart') > 0))
	{
		aaMozWrap($textEditor, 'a', ' href="'+enterURL+'"')
		return;
	}
	else {
		var enterTITLE = window.prompt(aaHelp['text_enter_url_name'], 'Link title');
		if(!enterTITLE) {
			FoundErrors += "\n" + aaHelp['error_no_title'];
		}
	}

	if (FoundErrors) {
		alert("Error!"+FoundErrors);
		return;
	}
	aaInsert('<a href="'+enterURL+'">'+ enterTITLE +'</a>', $textEditor);
}

function aaInsertEmail($textEditor)
{
	var emailAddress = window.prompt(aaHelp['text_enter_email'], "");
	if (!emailAddress)
	{
		window.alert(aaHelp['error_no_email']);
		return;
	}
	aaInsert('<a href="mailto:'+emailAddress+'">'+emailAddress+'</a>', $textEditor);
}

function aaInsertList($textEditor, type)
{
	var listvalue = "init";
	var thelist = "";

	while((listvalue != "") && (listvalue != null))
	{
		listvalue = prompt(aaHelp['text_enter_ulist'], "");
		if((listvalue != "") && (listvalue != null))
		{
			thelist = thelist+ "	<li>"+listvalue+"</li>\n";
		}
	}

	if(thelist != "")
		aaInsert("<"+type+">\n" + thelist + "</"+type+">\n", $textEditor);
}

function aaInsertTag($textEditor, tag)
{
	$textEditor.focus();
	if(document.selection)
	{
		var range = document.selection.createRange();
		range.text = '<'+tag+'>' + range.text + '</'+tag+'>';
	}
	else if($textEditor.get(0).selectionEnd >= 0)
	{
		aaMozWrap($textEditor, tag, '');
	}
	else
	{
		$textEditor.val($textEditor.val() + '<'+ tag +'></'+ tag +'>');
		$textEditor.focus();
	}
}

function aaMozWrap($textEditor, tag, params)
{
	var textEditor = $textEditor.get(0);
	var scrollPos = textEditor.scrollTop;
	var selLength = textEditor.textLength;
	var selStart = textEditor.selectionStart;
	var selEnd = textEditor.selectionEnd;
	if (selEnd == 1 || selEnd == 2)
		selEnd = selLength;
	var s1 = ($textEditor.val()).substring(0, selStart);
	var s2 = ($textEditor.val()).substring(selStart, selEnd)
	var s3 = ($textEditor.val()).substring(selEnd, selLength);
	if(tag)
		var tmp = s1 + '<' + tag + params + '>' + s2 + '</' + tag + '>';
	else
		var tmp = s1 + s2 + params;	//inserting the ready fragment from Insert()
	$textEditor.val(tmp + s3);
	var i = tmp.length;
	$textEditor.get(0).setSelectionRange(i, i);
	$textEditor.attr('scrollTop', scrollPos);
	return;
}

function aaStoreCaret(textEl) {
	if(textEl.createTextRange)
		textEl.caretPos = document.selection.createRange().duplicate();
}

function aaImgUploadWindow($textEditor)
{
	var wtop = (screen.height/2)-(160/2)-20;
	var wleft = (screen.width/2)-(500/2);
	window.open('./?action=upload&field='+$textEditor.attr('name'), 'imguploadWindow'+Math.floor(Math.random()*10),'top='+wtop+', left='+wleft+',titlebar=no,toolbar=no,width=500,height=220,directories=no,status=no,scrollbars=no, resize=no,menubar=no');
}

function tabKeyHandler(e)
{
	if(e.keyCode == 9)
	{
		aaInsert("\t", $(this));
		if(e.preventDefault)
			e.preventDefault();
		return false;
	}
}
