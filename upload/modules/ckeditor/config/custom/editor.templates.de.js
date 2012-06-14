/**
 *  @module         ckeditor
 *  @version        see info.php of this module
 *  @authors        Michael Tenschert, Dietrich Roland Pehlke
 *  @copyright      2010-2011 Michael Tenschert, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *	@version		$Id$
 */

// Register a templates definition set named "default".
CKEDITOR.addTemplates( 'default',
{
	// The name of sub folder which hold the shortcut preview images of the
	// templates.
	imagesPath : CKEDITOR.getUrl( CKEDITOR.plugins.getPath( 'templates' ) + '/templates/images/' ),

	// The templates definitions.
	templates :
		[

			{
				title: 'Textumfluss beenden DIV',
				image: 'template9.gif',
				description: 'Erstellt eine DIV mit clear:both. und einem neuen Absatz',
				html:
					'<div style="clear:both; height:1px; overflow:hidden;">&nbsp</div>' +
					'<p>...</p>'
			},
			{
				title: 'Textumfluss beenden BR',
				image: 'template9.gif',
				description: 'Erstellt eine BR mit clear:both',
				html:
					'<br style="clear:both;" />...'
			},
			{
				title: 'Bild links mit Untertitel',
				image: 'template1.gif',
				description: 'Bild links mit Bild&uuml;berschrift, Unterschrift  und Textumfluss. Die Box passt sich der Bildgr&ouml;&szlig;e an.',
				html:
					'<div>' +
						'<div style="border:1px solid #ccc; float: left; margin-right:10px; margin-bottom:5px;padding:5px; background-color:#ffffff; text-align:center; font-size:80%;">' +
							'&Uuml;berschrift<br />' +
							'<img src="/modules/ckeditor/ckeditor/plugins/templates/templates/images/no_image.jpg" border="0" align="center"/>' +
							'<br />Unterschrift' +
						'</div>' +
						'<p>' +
							'Text' +
						'</p>' +
					'<div style="clear:left; height:1px; overflow:hidden;">&nbsp</div>' +
					'</div>'+
					'<p>...</p>'
			},
			{
				title: 'Bild rechts mit Untertitel',
				image: 'template4.gif',
				description: 'Bild rechts mit Bild&uuml;berschrift, Unterschrift und Textumfluss.Die Box passt sich der Bildgr&ouml;&szlig;e an.',
				html:
					'<div>' +
						'<div style="border:1px solid #ccc; float: right; margin-left:10px; margin-bottom:5px;padding:5px; background-color:#ffffff; text-align:center; font-size:80%;">' +
							'&Uuml;berschrift<br />' +
							'<img src="/modules/ckeditor/ckeditor/plugins/templates/templates/images/no_image.jpg" border="0" align="center"/>' +
							'<br />Unterschrift' +
						'</div>' +
						'<p>' +
							'Text' +
						'</p>' +
					'<div style="clear:right; height:1px; overflow:hidden;">&nbsp</div>' +
					'</div>'+
					'<p>...</p>'
			},
			{
				title: 'Zweispaltiger Bereich',
				image: 'template2.gif',
				description: 'Zweispaltiger Bereich mit DIV, darunter normaler Text. Starre Weite, Inhalt l&auml;uft &uuml;ber den Rand der Boxen. Einziger Nachteil wenn man die Block Ansicht einschaltet verrutscht es.(by Yetiie)',
				html:
					'<div style="float: left; width: 47%;">' +
						'<p>Text1</p>' +
					'</div>' +
					'<div style="float: left; width: 47%; margin-left: 5.9%;">' +
					'	<p>Text2</p>' +
					'</div>' +
					'<div style="clear:both; height:1px; overflow:hidden;">&nbsp</div>' +
					'<p>...</p>'
			},
			{
				title: '2 Spalten Template',
				image: 'template2.gif',
				description: '2 Spalten Template mit Tabelle, darunter normaler Text. Versucht sich unterschiedlichen Breiten anzupassen. H&ouml;he bleibt Synchron,',
				html:
					'<table cellspacing="0" cellpadding="0" style="width:100%" border="1">' +
						'<tr>' +
							'<td style="width:49%" valign="top">' +
								'<h3>&Uuml;berschrift 1</h3>' +
							'</td>' +
							'<td style="width:2%"></td>' +
							'<td style="width:49%"valign="top" >' +
								'<h3>&Uuml;berschrift 2</h3>' +
							'</td>' +
						'</tr>' +
						'<tr>' +
							'<td valign="top">' +
								'Text 1' +
							'</td>' +
							'<td></td>' +
							'<td valign="top">' +
								'Text 2' +
							'</td>' +
						'</tr>' +
					'</table>' +
					'<p>...</p>'
			},

			{
				title: 'Dreispaltiger Bereich',
				image: 'template13.gif',
				description: 'Dreispaltiger Bereich mit DIV, darunter normaler Text. Starre Weite, Inhalt l&auml;uft &uuml;ber den Rand der Boxen. Einziger Nachteil wenn man die Block Ansicht einschaltet verrutscht es.(by Yetiie)',
				html:
					'<div style="float: left; width: 31%;">' +
						'<p>Text1</p>' +
					'</div>' +
					'<div style="float: left; width: 31%; margin-left: 3.4%">' +
						'<p>Text2</p>' +
					'</div>' +
					'<div style="float: left; width: 31%; margin-left: 3.4%;">' +
						'<p>Text3</p>' +
					'</div>' +
					'<div style="clear:both; height:1px; overflow:hidden;">&nbsp</div>' +
					'<p>...</p>'
			},
			{
				title: 'Infospalte rechts',
				image: 'template15.gif',
				description: 'Zweispaltiger Bereich mit DIV im 2/3 1/3 Mix, darunter normaler Text. Starre Weite, Inhalt l&auml;uft &uuml;ber den Rand der Boxen. Einziger Nachteil wenn man die Block Ansicht einschaltet verrutscht es.(by Yetiie)',
				html:
					'<div style="float: left; width:60%;">' +
						'<p>TextLinkeSpalte</p>' +
					'</div>' +
					'<div style="float: left; width: 35%; margin-left: 4.9%; font-size: 0.8em;">' +
						'<p>TextRechteSpalte</p>' +
					'</div>' +
					'<div style="clear:both; height:1px; overflow:hidden;">&nbsp</div>' +
					'<p>...</p>'
			},
			{
				title: 'Infospalte links',
				image: 'template16.gif',
				description: 'Zweispaltiger Bereich mit DIV im 2/3 1/3 Mix, darunter normaler Text. Starre Weite, Inhalt l&auml;uft &uuml;ber den Rand der Boxen. Einziger Nachteil wenn man die Block Ansicht einschaltet verrutscht es.',
				html:
					'<div style="float: left; width:35%;">' +
						'<p>TextLinkeSpalte</p>' +
					'</div>' +
					'<div style="float: left; width: 60%; margin-left: 4.9%; font-size: 0.8em;">' +
						'<p>TextRechteSpalte</p>' +
					'</div>' +
					'<div style="clear:both; height:1px; overflow:hidden;">&nbsp</div>' +
					'<p>...</p>'
			},
			{
				title: 'Text und Tabelle links',
				image: 'template5.gif',
				description: 'Tabelle links mit &uuml;berschrift, Unterschrift und Textumfluss. Weite kann &uuml;ber die Tabelleneinstellungen angepasst werden m&ouml;glichst Pixel auf keinen Fall Prozent.',
				html:
					'<div>' +
						'<div style="border:1px solid #ccc; float: left; margin-right:10px; margin-bottom:5px;padding:5px; background-color:#ffffff; text-align:center; font-size:80%;">' +
							'&Uuml;berschrift' +
							'<table style="width:250px;" cellspacing="0" cellpadding="0" border="1">' +
								'<tbody>' +
									'<tr>' +
										'<td>...</td>' +
										'<td>...</td>' +
										'<td>...</td>' +
									'</tr>' +
									'<tr>' +
										'<td>...</td>' +
										'<td>...</td>' +
										'<td>...</td>' +
									'</tr>' +
									'<tr>' +
										'<td>...</td>' +
										'<td>...</td>' +
										'<td>...</td>' +
									'</tr>' +
								'</tbody>' +
							'</table>' +
							'Hier der Text' +
						'</div>' +
						'<div style="clear:left; height:1px; overflow:hidden;">&nbsp</div>' +
					'</div>'+
					'<p>...</p>'
			},
			{
				title: 'Text und Tabelle rechts',
				image: 'template3.gif',
				description: 'Tabelle rechts mit &Uuml;berschrift, Unterschrift und Textumfluss. Weite kann &uuml;ber die Tabelleneinstellungen angepasst werden m&ouml;glichst Pixel auf keinen Fall Prozent.',
				html:
					'<div>' +
						'<div style="border:1px solid #ccc; float: right; margin-left:10px; margin-bottom:5px;padding:5px; background-color:#ffffff; text-align:center; font-size:80%;">' +
							'&Uuml;berschrift' +
							'<table style="width:250px;" cellspacing="0" cellpadding="0" border="1">' +
								'<tbody>' +
									'<tr>' +
										'<td>&nbsp;</td>' +
										'<td>&nbsp;</td>' +
										'<td>&nbsp;</td>' +
									'</tr>' +
									'<tr>' +
										'<td>&nbsp;</td>' +
										'<td>&nbsp;</td>' +
										'<td>&nbsp;</td>' +
									'</tr>' +
									'<tr>' +
										'<td>&nbsp;</td>' +
										'<td>&nbsp;</td>' +
										'<td>&nbsp;</td>' +
									'</tr>' +
								'</tbody>' +
							'</table>' +
							'Hier der Text' +
						'</div>' +
						'<div style="clear:right; height:1px; overflow:hidden;">&nbsp</div>' +
					'</div>'+
					'<p>...</p>'
			},
			{
				title: 'Text und Textfeld links',
				image: 'template7.gif',
				description: 'Textfeld links mit &Uuml;berschrift, Unterschrift und Textumfluss. Weite kann &uuml;ber die innere Box definiert werden m&ouml;glichst Pixel auf keinen Fall Prozent oder &uuml; die &Auml;u&szlig;ere Box oder einfach &uuml;ber den Inhalt.',
				html:
					'<div>' +
						'<div style="border:1px solid #ccc; float: left; margin-right:10px; margin-bottom:5px;padding:5px; background-color:#ffffff; text-align:center; font-size:80%;">' +
							'&Uuml;berschrift' +
							'<div style="border:1px solid #ccc; padding:3px;">' +
									'<p>Textfeld<br />Inhalt</p>' +
							'</div>' +
							'Unterschrift' +
						'</div>' +
						'<p>' +
							'Hier der Text' +
						'</p>' +
						'<div style="clear:left; height:1px; overflow:hidden;">&nbsp</div>' +
					'</div>'+
					'<p>...</p>'
			},
			{
				title: 'Text und Textfeld rechts',
				image: 'template8.gif',
				description: 'Textfeld links mit &Uuml;berschrift, Unterschrift und Textumfluss. Weite kann &uuml;ber die innere Box definiert werden m&ouml;glichst Pixel auf keinen Fall Prozent oder &uuml; die &Auml;u&szlig;ere Box oder einfach &uuml;ber den Inhalt.',
				html:
					'<div>' +
						'<div style="border:1px solid #ccc; float: right; margin-left:10px; margin-bottom:5px;padding:5px; background-color:#ffffff; text-align:center; font-size:80%;">' +
							'&Uuml;berschrift' +
							'<div style="border:1px solid #ccc; padding:3px;">' +
									'<p>Textfeld<br />Inhalt</p>' +
							'</div>' +
							'Unterschrift' +
						'</div>' +
						'<p>' +
							'Hier der Text' +
						'</p>' +
						'<div style="clear:right; height:1px; overflow:hidden;">&nbsp</div>' +
					'</div>'+
					'<p>...</p>'
			},
			{
				title: 'Tabelle zentriert, einspaltig',
				image: 'template11.gif',
				description: 'Eine zentrierte einspaltige Tabelle f&uuml;r beliebige Inhalte.Tabelle w&auml;chst dem Inhalt entsprechend.',
				html:
					'<div style="text-align: center;">' +
						'<table align="center" style="width: 50%; margin: 0pt auto; background:#ffffff;" border="1" cellpadding="5" cellspacing="0" style="">' +
							'<tbody>' +
							'<tr><td>Inhalt</td></tr>' +
							'</tbody>' +
						'</table>' +
					'</div>'+
					'<p>...</p>' 
			},
			{
				title: 'Bild zentriert',
				image: 'template14.gif',
				description: 'Eine zentrierte Tabelle mit Bild.Tabelle w&auml;chst dem Inhalt entsprechend und bleibt trotzdem mittig deswegen muss der Anwender nicht mit irgendwelchen Einstellungen rumbasteln.',
				html:
					'<div style="text-align: center;">' +
						'<table align="center" style="width: 50%; margin: 0pt auto; background:#ffffff;" border="1" cellpadding="5" cellspacing="0" style="">' +
							'<tbody>' +
							'<tr><td style="font-size:80%;" align="center">' +
								'&Uuml;berschrift<br />' +
								'<img src="/modules/ckeditor/ckeditor/plugins/templates/templates/images/no_image.jpg" border="0" align="center"/>' +
								'<br />Unterschrift' +
							'</td></tr>' +
							'</tbody>' +
						'</table>' +
					'</div>'+
					'<p>...</p>' 			},
			{
				title: 'Textfeld zentriert',
				image: 'template12.gif',
				description: 'Textfeld zentriert auf Basis eines DIV. Die Gr&ouml;&szlig;e muss &uuml;ber die &Auml;u&szlig;ere Box definiert werden das macht es bei Bildern ziemlich umst&auml;ndlich.',
				html:
					'<div style="text-align: center;">' +
						'<div style="border:1px solid #ccc; margin: 0pt auto;padding:5px; background:#ffffff;width:50%;text-align:center; font-size:80%;" >' +
							'&Uuml;berschrift' +
							'<div style="border:1px solid #ccc; padding:3px;">Dies ist ein Textfeld.<br />blablabla</div>' +
							'Unterschrift' +
						'</div>' +
					'</div>'+
					'<p>...</p>'
			}
		]
});
