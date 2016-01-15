// JavaScript Document
var q = jQuery.noConflict();

var ajaxQueue = (function() {
     var requests = [];

     return {
        addReq:  function(opt) {
            requests.push(opt);
        },
        removeReq:  function(opt) {
            if( q.inArray(opt, requests) > -1 )
                requests.splice(q.inArray(opt, requests), 1);
        },
        run: function() {
            var self = this,
                orgSuc;

            if( requests.length ) {
                oriSuc = requests[0].complete;

                requests[0].complete = function() {
                     if( typeof oriSuc === 'function' ) oriSuc();
                     requests.shift();
                     self.run.apply(self, []);
                };   

                q.ajax(requests[0]);
            } else {
              self.tid = setTimeout(function() {
                 self.run.apply(self, []);
              }, 1000);
            }
        },
        stop:  function() {
            requests = [];
            clearTimeout(this.tid);
        }
     };
}());