// you can override default options globally, so they apply to every .expander() call
$.expander.defaults.slicePoint = 120;

$(document).ready(function() {
  // simple example, using all default options unless overridden globally
  //$('div.expandable p').expander();

  // *** OR ***
  
  

  // override default options (also overrides global overrides)
  //$('div.expandable p:gt(0)').expander({
   // slicePoint:       80,  // default is 100
    //expandPrefix:     ' ', // default is '... '
    //expandText:       '[...]', // default is 'read more'
    //collapseTimer:    5000, // re-collapses after 5 seconds; default is 0, so no re-collapsing
    //userCollapseText: '[^]'  // default is 'read less'
 // });
 
 //Select the <h1>s greater than (gt) the first one (numbered 0) plus everything that follows until the next <h1>
  
   $('.announcement h1:gt(0)~*').expander({
    //slicePoint:       80,  // default is 100

    expandPrefix: '...',

  });


});