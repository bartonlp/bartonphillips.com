// BLP 2014-03-06 -- track user activity

(function($) {
  var id;
  var ajaxfile = "tracker.php";
  
  $(window).load(function(e) {
    // Track the ip/agent/page
    var self = window.location.pathname,
      referrer = document.referrer;

    $.ajax({
      url: ajaxfile,
      data: {page: 'load', self: self, referrer: referrer },
      type: 'post',
      success: function(data) {
             console.log(data);
             id = data;
           },
           error: function(err) {
             console.log(err);
           }
    });
  });

  $(window).unload(function(e) {
    $.ajax({
      url: ajaxfile,
      data: {page: 'unload', id: id },
      type: 'post',
      async: false,
      success: function(data) {
             console.log(data);
           },
           error: function(err) {
             console.log(err);
           }
    });
  });
})(jQuery);
