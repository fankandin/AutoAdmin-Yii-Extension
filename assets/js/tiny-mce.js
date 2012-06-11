$(document).ready(function() {
	$('#mainform').submit(function() {
		var iframes = document.getElementsByTagName('iframe');
		for(var i=0; i<iframes.length; i++)
		{
			if(iframes[i].id.substr(0, 7) == 'editor_')
			{
				if(iframes[i].contentWindow.tinyMCE.get('editor').isHidden())
					iframes[i].contentWindow.tinyMCE.get('editor').show();
				var t = iframes[i].contentWindow.tinyMCE.get('editor').getContent();
				//t = t.replace(/href\=\"(\.\.\/)+/g, 'href="/');
				//t = t.replace(/^<p>[\r\n]*<p>/g, '<p>');
				//t = t.replace(/<\/p>[\r\n]*<\/p>$/g, '</p>');
				//t = t.replace(/<p>[\r\n\s]*<\/p>/g, '');
				this[iframes[i].id.substr(7)].innerHTML = t;
			}
		}
	});
});