/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';


	config.skin            = "moonocolor";
	config.language        = "en";
	config.height          = "300px";
	config.removePlugins   = "about,print,save,newpage,templates,forms";
	config.extraPlugins    = "youtube,oembed";
	config.allowedContent  = true;
	config.enterMode       = 2;
	config.autoParagraph   = false;
	config.entities        = false;
	config.fillEmptyBlocks = false;
	config.baseHref        = "";
	config.contentsCss     = "";

	// Toolbar groups configuration.
	config.toolbarGroups = [
					{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
					//{ name: 'clipboard', groups: [ 'clipb oard', 'undo' ] },
					//{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
					{ name: 'forms' },
					{ name: 'insert' },
					// '/',
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align' ] },
					{ name: 'links' },
					// '/',
					{ name: 'styles' },
					{ name: 'colors' },
					//{ name: 'tools' },
					//{ name: 'others' },
					{ name: 'about' }
			      ];
};
