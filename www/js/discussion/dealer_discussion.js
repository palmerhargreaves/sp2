DealerDiscussion = function(config) {
  // configurable {
  this.dealer_discussion_url = null; // required url to request discussion id by dealer id
  // }
  
  DealerDiscussion.superclass.constructor.call(this, config);
  
  this.start_message = false;
}

utils.extend(DealerDiscussion, Discussion, {
  startDiscussionWithDealer: function(id, start_message) {
    this.start_message = start_message;
    
    $.post(this.dealer_discussion_url, { id: id })
     .success($.proxy(this.onReceiveDealerDiscussion, this))
     .error(this.onReceiveDealerDiscussionError, this);
  },
  
  onReceiveDealerDiscussion: function(data) {
    this.startDiscussion(data.id, this.start_message);
  },
  
  onReceiveDealerDiscussionError: function() {
    alert('Ошибка получения номера чата');
  }
});