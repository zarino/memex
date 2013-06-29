String.prototype.startsWith = function(str){
  return this.slice(0, str.length) == str
}

String.prototype.endsWith = function(str){
  return this.slice(-str.length) == str
}

String.prototype.strip = function(){
  return $.trim(this)
}

function logIn(){
  var $input = $('input#apikey')
  var apikey = $input.val()

  if(apikey.strip() == ''){
    wrongApikey()
  } else {
    $.ajax({
      url: '/api/items',
      type: 'GET',
      data: { apikey: apikey },
      dataType: 'json',
      success: loginSuccess,
      error: loginFailure
    })
  }
}

var loginSuccess = function(data){
  var $form = $('input#apikey').parent()
  $form.addClass('animated fadeOutUp')
  setTimeout(function(){
    $form.hide().removeClass('animated fadeOutUp')
  }, 1000)
}

var loginFailure = function(jqXHR, errorType, textStatus){
  if(jqXHR.status == 403){
    // invalid apikey!
    wrongApikey()
  } else {
    console.log(jqXHR, errorType, textStatus)
  }
}

var wrongApikey = function(){
  var $input = $('input#apikey')
  var $form = $input.parent()

  $form.addClass('animated shake error')
  setTimeout(function(){
    $form.removeClass('animated shake')
    $input.select()
  }, 1000)
}

$(function(){
  $('input#apikey').focus().on('keyup', function(e){
    if(e.which == 13){ logIn() }
    $(this).parent().removeClass('error')
  }).next().on('click', logIn)
})
