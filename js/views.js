Memex.View.App = Backbone.View.extend({
  el: 'body',
  renderChild: function(child) {
    if(this._currentChild){
      this._currentChild.remove()
    }
    this._currentChild = child
    this.$el.html(child.render().el)
  }
})

Memex.View.Login = Backbone.View.extend({
  className: 'container login',

  events: {
    'click button': 'logIn',
    'keyup #apikey': 'checkKeyup'
  },

  render: function(){
    this.$el.html('<h1>memex</h1><div class="input-append control-group"><input type="text" id="apikey" placeholder="API key" /><button class="btn" type="button">Log in</button></div>')
    return this
  },

  logIn: function(e){
    var _this = this
    var apikey = $('#apikey').val()

    if(apikey.strip() == ''){
      this.wrongApikey()
    } else {
      $.ajax({
        url: '/api/items',
        type: 'GET',
        data: { apikey: apikey },
        dataType: 'json',
        success: function(data){
          _this.$el.addClass('animated fadeOutUp')
          setTimeout(function(){
            _this.$el.hide().removeClass('animated fadeOutUp')
          }, 1000)
        },
        error: function(jqXHR, errorType, textStatus){
          if(jqXHR.status == 403){
            _this.wrongApikey()
          } else {
            console.log(jqXHR, errorType, textStatus)
          }
        }
      })
    }
  },

  checkKeyup: function(e){
    if(e.which == 13){
      this.logIn()
    } else {
      $('#apikey').parent().removeClass('error')
    }
  },
  
  wrongApikey: function(){
    var $input = $('#apikey')
    var $form = $input.parent()
    $form.addClass('animated shake error')
    setTimeout(function(){
      $form.removeClass('animated shake')
      $input.select()
    }, 1000)
  }

})