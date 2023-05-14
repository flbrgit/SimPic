/*
 * jQuery Elements Grid plugin
 *
 * Contribute: https://bitbucket.org/BobroCoder/jqelgrid
 *
 * @license: MIT license: http://opensource.org/licenses/MIT
 *
 */


(function($){
  jQuery.fn.jqElGrid = function( settings ){

    var settings = $.extend( {
      child             :   '.item',
      elSize            :   200,
      elPadding         :   5,
      rowsShow          :   9999,
      hidedClass        :   'jqElGrid-hided',
      waitTime          :   250,
      oneColumnHeight   :   0.6,
      prioAttr          :   'prio',
      columns           :   0
    }, settings);
    var objects = {
      selector          :   null,
      parentSize        :   0,
      columns           :   0
    };

    //Random between two numbers
    var randomInt = function(min,max) {
        return Math.floor(Math.random()*(max-min+1)+min);
    }

    //End event waiting function
    var waitForFinalEvent = (function() {
      var timer = null;
      return function(callback, ms) {
        if (timer) clearTimeout(timer);
        timer = setTimeout(callback, ms);
      };
    })();

    //Get priorites map
    var getPrioMap = function(){
      var elsPriorites = [], prioMap = [], elsNeitral = [];
      //Get prios from data attributes
      $(objects.selector+' '+settings.child).each(function(index) {
        var prio = $(this).data(settings.prioAttr);
        if ((typeof prio !== "undefined")&&(prio!=0)){
          elsPriorites.push({'elIndex':index,prio:prio});
        }else elsNeitral.push(index);
      });
      //Sort priorites list
      elsPriorites.sort(function (a, b) {
        if (a.prio < b.prio) return 1;
        if (a.prio > b.prio) return -1;
        return 0;
      });
      //Add positive priorites
      $.each(elsPriorites,function(){
        if (this.prio>0) prioMap.push(this.elIndex);
      });
      //Add neitral priorites
      while (elsNeitral.length>0){
        var index = randomInt(0,elsNeitral.length-1);
        prioMap.push(elsNeitral[index]);
        elsNeitral = elsNeitral.filter(function(val,ind){
            return ind!=index;
        });
      }
      //Add negative priorites
      $.each(elsPriorites,function(){
        if (this.prio<0) prioMap.push(this.elIndex);
      });
      //Return priorites map
      return prioMap;
    }

    //Get result map
    var getItemsArr = function(modified){
      var length = $(objects.selector+' '+settings.child).length;
      var gridArr = [], index = 0, position = 0;
      while (index < length){
        if (modified.indexOf(index)!=-1){
          gridArr.push(index);
        }
        gridArr.push(index);
        index=index+1;
      }
      return gridArr;
    }

    //Check edges
    var checkEdge = function(modified){
      var result = true,
          data = getItemsArr(modified);
      for (var i = 0; i < modified.length; i++) {
        var itemIndex = data.indexOf(modified[i])+1;
        if (itemIndex%objects.columns == 0){
          result = false;
          break;
        }
      }
      return result;
    }

    //Get modified items
    var getModifiedItems = function(){
      var prioMap = getPrioMap();
      var remain = $(objects.selector+' '+settings.child).length;
      remain = objects.columns - remain%objects.columns;
      var modified = [], iteration = null;
      while (modified.length < remain){
        if (iteration === null) iteration = 0;
        if (iteration > prioMap.length){
          prioMap = getPrioMap();
          modified = [];
          iteration = 0;
        }
        for (var i = 0; i < prioMap.length; i++) {
          var mod = modified.concat();
          mod = mod.concat([prioMap[i]]);
          if (checkEdge(mod)&&(modified.indexOf(prioMap[i])==-1)){
            modified.push(prioMap[i]);
            iteration = 0;
            break;
          }else iteration=iteration+1;
        }
      }
      return modified;
    }

    var renderGrid = function(modified){
      var size = 100/(objects.columns);
      $(objects.selector+' '+settings.child).css({
        'float':'left',
        'width':size+'%'
      });
      var height = $(objects.selector+' '+settings.child).width();
      if (objects.columns==1) height = height*settings.oneColumnHeight;
      $(objects.selector+' '+settings.child).css('height', height);
      $.each(modified,function(){
        $(objects.selector+' '+settings.child).eq(this).width((size*2)+'%');
      });
    }

    var setPaddings = function(){
      var padding = settings.elPadding/objects.parentSize*100;
      padding = padding.toFixed(6);
      var remain = 100;
      $(objects.selector+' '+settings.child).each(function(){
        var newWidth = parseFloat($(this)[0].style.width);
        remain = remain - newWidth;
        if (remain.toFixed(6) != 0)
          $(this).width( (newWidth - padding)+'%' );
        $(this).css({
          'margin-right':padding+'%',
          'margin-bottom':settings.elPadding
        });
        if (remain.toFixed(6) == 0){
          $(this).css('margin-right',0);
          remain = 100;
        }
      });
    }

    var setHideRows = function(){
      var row = 1, offset = null,
          showCount = settings.rowsShow;
      $(objects.selector+' '+settings.child).show();
      $(objects.selector+' '+settings.child).each(function(){
        if ( (offset!==null) && ($(this).offset().top!=offset) ){
          row = row+1;
        }
        offset = $(this).offset().top;
        if ((showCount>0)&&(row>showCount)){
          $(this).hide().addClass(settings.hidedClass);
        }
      });
    }

    var showCatalog = (function() {
      var columns = $(objects.selector).data('columns');
      if ((typeof columns !== "undefined")&&(columns!=0))
        objects.columns = columns;
      else{
        if (settings.columns>0) objects.columns = settings.columns;
        else objects.columns = ~~(objects.parentSize/(settings.elSize));
      }
      var modified = [];
      if (objects.columns>1) modified = getModifiedItems();
      renderGrid(modified);
      setPaddings();
      setHideRows();
    });

    //Initialize plugin
    objects.selector = $(this).selector;
    var elementsCount = $(objects.selector+' '+settings.child).length;
    if (elementsCount>0){
      if ($(objects.selector+' '+settings.child))
      $(objects.selector).append('<span style="clear:both"></span>');
      objects.parentSize = $(this).width();
      showCatalog();
      $(window).resize(function(){
        waitForFinalEvent(function() {
          objects.parentSize = $(this).width();
          showCatalog();
        }, settings.waitTime);
      });
    }
  };
})(jQuery);