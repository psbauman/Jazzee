/**
 * The TextPage type
  @extends ApplyPage
 */
function TextPage(){}
TextPage.prototype = new ApplyPage();
TextPage.prototype.constructor = TextPage;

/**
 * Create the page workspace
 * For text pages we don't have a lot of options
 */
TextPage.prototype.workspace = function(){
  this.clearWorkspace();
  $('#workspace-left-top').append(this.titleBlock());
  $('#workspace-left-top').append(this.textInputBlock('leadingText', 'click to edit'));
  $('#workspace-left-bottom-left').append(this.textAreaBlock('trailingText', 'click to edit'));
  
  $('#workspace-right-top').append(this.copyPageBlock());
  $('#workspace-right-top').append(this.previewPageBlock());
  $('#workspace-right-bottom').append(this.deletePageBlock());
  $('#workspace').show('slide');
};