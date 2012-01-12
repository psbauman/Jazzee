/**
 * The JazzeePageBranching type
  @extends JazzeePage
 */
function JazzeePageBranching(){}
JazzeePageBranching.prototype = new JazzeePage();
JazzeePageBranching.prototype.constructor = JazzeePageBranching;

/**
 * Override AplyPage::newPage to set varialbe defaults
 * @param {String} id the id to use
 * @returns {JazzeePageBranching}
 */
JazzeePageBranching.prototype.newPage = function(id,title,classId,className,status,pageStore){
  var page = JazzeePage.prototype.newPage.call(this, id,title,classId,className,status,pageStore);
  page.setVariable('branchingElementLabel', title);
  return page;
};

JazzeePageBranching.prototype.workspace = function(){
  //call the parent workspace method
  JazzeePage.prototype.workspace.call(this);
  $('#workspace-right-top').append(this.selectListBlock('isRequired', 'This page is', {0:'Optional',1:'Required'}));
  $('#workspace-right-top').append(this.selectListBlock('answerStatusDisplay', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  
  
  $('#workspace-left-middle-left').show();
  $('#workspace-left-middle-left').append(this.textInputVariableBlock('branchingElementLabel', 'Branching Element Label: ', 'click to edit'));
  $('#workspace-left-middle-left').append(this.listBranchesBlock());
};

JazzeePageBranching.prototype.listBranchesBlock = function(){
  var div = $('<div>').append($('<h5>').html('Branched Pages'));
  var pageClass = this;
  var ol = $('<ol>').addClass('page-list');
  for(var i in this.children){
    var branch = this.children[i];
    var li = $('<li>').html(branch.title);
    li.data('page', branch);
    $(li).bind('click',function(){
      var page = $(this).data('page');
      page.workspace();
      //get rid of the delete pages box and add a delete branch box
      var deletep = $('<p>Delete this branch</p>').addClass('delete').bind('click',{branch: page}, function(e){
          $('#workspace').effect('explode',500);
          pageClass.deleteChild(e.data.branch);
          pageClass.workspace();
      });
      $('#workspace-right-bottom p.delete').remove();
      $('#workspace-right-bottom').append(deletep);
    });
    ol.append(li);
  }
  var p = $('<p>').addClass('add').html('New Branch').bind('click',function(){
	var standardPageTypeId = pageClass.pageStore.getPageType('JazzeePageStandard');
    var branch = new JazzeePageStandard.prototype.newPage('newpage' + pageClass.pageStore.getUniqueId(),'New Branch',standardPageTypeId,'JazzeePageStandard','new',pageClass.pageStore);
    pageClass.addChild(branch);
    div.replaceWith(pageClass.listBranchesBlock());
  });
  return div.append(ol).append(p);
};