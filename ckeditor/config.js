/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
  // config.uiColor = '#AADC6E';
  //config.toolbar = 'Barton';
  
  config.toolbar_Barton =
                         [
                          ['Source','-','Preview', '-', 'Save'],
                          ['Print'],
                          ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                          ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
                          '/',
                          ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                          ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
                          ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                          ['Link','Unlink','Anchor'],
                          ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
                          '/',
                          ['Styles','Format','Font','FontSize'],
                          ['TextColor','BGColor'],
                          ['Maximize', 'ShowBlocks','-','About']
                         ];

  config.toolbar_Kremmling =
                         [
                          ['Source','-','Preview'],
                          ['Print'],
                          ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                          '/',
                          ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                          ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
                          ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                          ['Link','Unlink','Anchor'],
                          ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
                          '/',
                          ['Styles','Format','Font','FontSize'],
                          ['TextColor','BGColor'],
                          ['Maximize', 'ShowBlocks','-','About']
                         ];

};
